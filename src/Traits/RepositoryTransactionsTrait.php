<?php namespace Znck\Repositories\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use Znck\Repositories\Exceptions\DeleteResourceFailedException;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Znck\Repositories\Exceptions\ResourceFailedException;
use Znck\Repositories\Exceptions\StoreResourceFailedException;
use Znck\Repositories\Exceptions\UpdateResourceFailedException;

/**
 * @internal
 */
trait RepositoryTransactionsTrait
{
    /**
     * @param $errors
     * @param $e
     *
     * @return \Znck\Repositories\Exceptions\ResourceFailedException
     */
    protected function makeResourceFailedException($errors, $e)
    {
        if ($class = config('repository.errors.fallback')) {
            return $this->app->make($class, [null, $errors, $e]);
        }

        return new ResourceFailedException(null, $errors, $e);
    }

    /**
     * @param $errors
     * @param $e
     *
     * @return \Znck\Repositories\Exceptions\DeleteResourceFailedException
     */
    protected function makeDeleteResourceFailedException($errors, $e)
    {
        if ($class = config('repository.errors.delete')) {
            return $this->app->make($class, [null, $errors, $e]);
        }

        return new DeleteResourceFailedException(null, $errors, $e);
    }

    /**
     * @param $errors
     * @param $e
     *
     * @return \Znck\Repositories\Exceptions\UpdateResourceFailedException
     */
    protected function makeUpdateResourceFailedException($errors, $e)
    {
        if ($class = config('repository.errors.update')) {
            return $this->app->make($class, [null, $errors, $e]);
        }

        return new UpdateResourceFailedException(null, $errors, $e);
    }

    /**
     * @param $errors
     * @param $e
     *
     * @return \Znck\Repositories\Exceptions\StoreResourceFailedException
     */
    protected function makeCreateResourceFailedException($errors, $e)
    {
        if ($class = config('repository.errors.create')) {
            return $this->app->make($class, [null, $errors, $e]);
        }

        return new StoreResourceFailedException(null, $errors, $e);
    }
    
    /**
     * @param $errors
     * @param $e
     *
     * @return \Znck\Repositories\Exceptions\NotFoundResourceException
     */
    protected function makeNotFoundResourceException($errors, $e)
    {
        if ($class = config('repository.errors.not-found')) {
            return $this->app->make($class, [null, $errors, $e]);
        }

        return new NotFoundResourceException;
    }

    /**
     * Run in a transaction.
     *
     * @param callable $callback
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function transaction($callback, Model &$model)
    {
        $arguments = func_get_args();

        array_splice($arguments, 0, 2);

        try {
            $this->runInTransaction($callback, $model, $arguments);
        } catch (Throwable $e) {
            if (! $this->shouldThrowException()) {
                return $model;
            }

            $errors = $this->getErrorsFromModel($model);
            switch ($this->getMethod()) {
                case 'create':
                    throw $this->makeCreateResourceFailedException($errors, $e);
                case 'update':
                    throw $this->makeUpdateResourceFailedException($errors, $e);
                case 'delete':
                    throw $this->makeDeleteResourceFailedException($errors, $e);
                default:
                    throw $this->makeResourceFailedException($errors, $e);
            }
        }

        return $model;
    }

    protected function parseSelf($model)
    {
        if (is_string($model)) {
            $model = $this->self()->find($model);
        }

        if (! $model instanceof $this->model) {
            throw $this->makeNotFoundResourceException([], null);
        }

        return $model;
    }

    protected function getErrorsFromModel(Model &$model)
    {
        if (method_exists($model, 'getErrors')) {
            return $model->getErrors();
        } elseif ($method = config('repository.error_method')) {
            return call_user_func([$model, $model]);
        }

        return [];
    }

    protected function shouldThrowException()
    {
        if (isset($this->throwError)) {
            return $this->throwError;
        }

        return true;
    }

    protected function beginTransaction()
    {
        DB::beginTransaction();
    }

    protected function commitTransaction()
    {
        DB::commit();
    }

    protected function rollbackTransaction()
    {
        DB::rollback();
    }

    protected function runInTransaction($callback, Model &$model, $arguments)
    {
        try {
            $this->beginTransaction();
            if (true !== call_user_func($callback, array_merge([$model], $arguments))) {
                throw new Exception();
            }
            $this->commitTransaction();
        } catch (Throwable $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    protected function getMethod()
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
    }
}
