<?php namespace Znck\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Znck\Repositories\Exceptions\DeleteResourceException;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Znck\Repositories\Exceptions\StoreResourceException;
use Znck\Repositories\Exceptions\UpdateResourceException;

/**
 * @property \Illuminate\Contracts\Container\Container $app
 */
trait RepositoryHelper
{
    protected $inTransaction = false;

    public function startTransaction() {
        $this->app->make('db')->beginTransaction();
        $this->inTransaction = true;
    }

    public function commitTransaction() {
        if (!$this->inTransaction) {
            $this->app->make('db')->commit();
            $this->inTransaction = false;
        }
    }

    public function rollbackTransaction() {
        if ($this->inTransaction) {
            $this->app->make('db')->rollback();
            $this->inTransaction = false;
        }
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes) {
        try {
            $this->validate($attributes);
        } catch (ValidationException $e) {
            throw new StoreResourceException('There is some problem with you input.', $e->validator->getMessageBag());
        }

        $instance = $this->app->make($this->model, [$attributes]);

        if (method_exists($this, 'creating')) {
            $status = $this->creating($instance, $attributes);
        } else {
            $status = $instance->save();
        }

        if ($status !== true) {
            $this->rollbackTransaction();
            throw new StoreResourceException('Server broke down while processing your input.');
        }

        $this->commitTransaction();

        return $instance;
    }

    /**
     * Update the model in the database.
     *
     * @param Model|string|int $id
     * @param array $attributes
     * @param array $options
     *
     * @return Model
     */
    public function update($id, array $attributes, array $options = []) {
        /* @var Model $instance */
        try {
            $instance = $id instanceof Model ? $id : $this->app->make(static::class)->find($id);
        } catch (NotFoundResourceException $e) {
            throw new UpdateResourceException('The resource you are trying to update does not exist.');
        }

        try {
            $this->validate($attributes, $instance);
        } catch (ValidationException $e) {
            throw new UpdateResourceException('There is some problem with you input.', $e->validator->getMessageBag());
        }

        if (method_exists($this, 'updating')) {
            $status = $this->updating($instance, $attributes, $options);
        } else {
            $status = $instance->update($attributes, $options);
        }

        if ($status !== true) {
            $this->rollbackTransaction();

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
                $this->rollbackTransaction();
                throw new DeleteResourceException('Cannot delete this resource.');
            }

            return $status;
        }

        if ($status = $instance->delete()) {
            $this->commitTransaction();

            return $status;
        }

        $this->rollbackTransaction();
        throw new DeleteResourceException('Server broke down while processing you input.');
    }
}
