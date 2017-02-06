<?php

namespace Znck\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Znck\Repositories\Contracts\HasTransactions;
use Znck\Repositories\Contracts\Validating;
use Znck\Repositories\Exceptions\DeleteResourceException;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Znck\Repositories\Exceptions\StoreResourceException;
use Znck\Repositories\Exceptions\UpdateResourceException;

trait ExtrasHelper
{
    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes)
    {
        if ($this instanceof Validating) {
            try {
                $this->validate($attributes);
            } catch (ValidationException $e) {
                throw new StoreResourceException('There is some problem with you input.', $e->validator->getMessageBag());
            }
        }

        $instance = $this->app->make($this->model, [$attributes]);

        if (method_exists($this, 'creating')) {
            $this->onCreate($this->creating($instance, $attributes));
        } else {
            $this->onCreate($instance->save());
        }

        return $instance;
    }

    protected function onCreate(bool $status)
    {
        if ($status !== true) {
            if ($this instanceof HasTransactions) {
                $this->rollbackTransaction();
            }

            throw new StoreResourceException('Server broke down while processing your input.');
        }

        if ($this instanceof HasTransactions) {
            $this->commitTransaction();
        }
    }

    /**
     * Update the model in the database.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|int $id
     * @param array                                          $attributes
     * @param array                                          $options
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $attributes, array $options = [])
    {
        try {
            $instance = $id instanceof Model ? $id : $this->app->make(static::class)->find($id);
        } catch (NotFoundResourceException $e) {
            throw new UpdateResourceException('The resource you are trying to update does not exist.');
        }

        if ($this instanceof Validating) {
            try {
                $this->validate($attributes, $instance);
            } catch (ValidationException $e) {
                throw new UpdateResourceException('There is some problem with you input.', $e->validator->getMessageBag());
            }
        }

        if (method_exists($this, 'updating')) {
            $this->onUpdate($this->updating($instance, $attributes, $options));
        } else {
            $this->onUpdate($instance->update($attributes, $options));
        }

        return $instance;
    }

    protected function onUpdate(bool $status)
    {
        if ($status !== true) {
            if ($this instanceof HasTransactions) {
                $this->rollbackTransaction();
            }

            if ($status !== true and ! $instance->isDirty()) {
                new UpdateResourceException('Nothing updated since last time.');
            }

            throw new UpdateResourceException('Server broke down while processing your input.');
        }

        if ($this instanceof HasTransactions) {
            $this->commitTransaction();
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param Model|string|int $id
     *
     * @return bool|null
     */
    public function delete($id)
    {
        try {
            $instance = $id instanceof Model ? $id : $this->app->make(static::class)->find($id);
        } catch (NotFoundResourceException $e) {
            throw new DeleteResourceException('The resource you are trying to update does not exist.');
        }

        if (method_exists($this, 'deleting')) {
            $this->onDelete($this->deleting($instance));
        } else {
            $this->onDelete($instance->delete());
        }

        return true;
    }

    protected function onDelete(bool $status)
    {
        if ($status !== true) {
            if ($this instanceof HasTransactions) {
                $this->rollbackTransaction();
            }

            throw new DeleteResourceException('Cannot delete this resource.');
        }

        if ($this instanceof HasTransactions) {
            $this->rollbackTransaction();
        }
    }
}
