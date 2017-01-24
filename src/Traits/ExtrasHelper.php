<?php

namespace Znck\Repositories\Traits;
use Illuminate\Database\Eloquent\Model;
use Znck\Repositories\Contracts\Validating;
use Znck\Repositories\Contracts\HasTransactions;
use Znck\Repositories\Exceptions\StoreResourceException;
use Znck\Repositories\Exceptions\UpdateResourceException;
use Znck\Repositories\Exceptions\DeleteResourceException;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Illuminate\Validation\ValidationException;

trait ExtrasHelper {
    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes) {
        if ($this instanceof Validating) {
            try {
                $this->validate($attributes);
            } catch (ValidationException $e) {
                throw new StoreResourceException('There is some problem with you input.', $e->validator->getMessageBag());
            }
        }

        $instance = $this->app->make($this->model, [$attributes]);

        if (method_exists($this, 'creating')) {
            $status = $this->creating($instance, $attributes);
        } else {
            $status = $instance->save();
        }

        if ($status !== true) {
            if ($this instanceof HasTransactions) $this->rollbackTransaction();

            throw new StoreResourceException('Server broke down while processing your input.');
        }

        if ($this instanceof HasTransactions) $this->commitTransaction();

        return $instance;
    }

    /**
     * Update the model in the database.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|int $id
     * @param array $attributes
     * @param array $options
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $attributes, array $options = []) {
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
            $status = $this->updating($instance, $attributes, $options);
        } else {
            $status = $instance->update($attributes, $options);
        }

        if ($status !== true) {
            if ($this instanceof HasTransactions) $this->rollbackTransaction();

            if ($status !== true and !$instance->isDirty()) {
                new UpdateResourceException('Nothing updated since last time.');
            }

            throw new UpdateResourceException('Server broke down while processing your input.');
        }

        $this->commitTransaction();

        return $instance;
    }

    /**
     * Delete the model from the database.
     *
     * @param Model|string|int $id
     *
     * @return bool|null
     */
    public function delete($id) {
        /* @var Model $instance */
        try {
            $instance = $id instanceof Model ? $id : $this->app->make(static::class)->find($id);
        } catch (NotFoundResourceException $e) {
            throw new DeleteResourceException('The resource you are trying to update does not exist.');
        }

        if (method_exists($this, 'deleting')) {
            $status = $this->deleting($instance);

            if ($status !== true) {
                if ($this instanceof HasTransactions) $this->rollbackTransaction();
                throw new DeleteResourceException('Cannot delete this resource.');
            }

            return $status;
        }

        if ($status = $instance->delete()) {
            if ($this instanceof HasTransactions) $this->commitTransaction();

            return $status;
        }

        if ($this instanceof HasTransactions) $this->rollbackTransaction();
        throw new DeleteResourceException('Server broke down while processing you input.');
    }
}
