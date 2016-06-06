<?php namespace Znck\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Znck\Repositories\Contracts\RepositoryCreateInterface;
use Znck\Repositories\Contracts\RepositoryCriteriaInterface;
use Znck\Repositories\Contracts\RepositoryDeleteInterface;
use Znck\Repositories\Contracts\RepositoryQueryInterface;
use Znck\Repositories\Contracts\RepositoryUpdateInterface;
use Znck\Repositories\Exceptions\RepositoryException;
use Znck\Repositories\Traits\MutationHelperTrait;
use Znck\Repositories\Traits\RepositoryCriteriaTrait;
use Znck\Repositories\Traits\RepositoryQueryTrait;
use Znck\Repositories\Traits\RepositoryTransactionsTrait;

abstract class Repository implements RepositoryQueryInterface, RepositoryCriteriaInterface, RepositoryCreateInterface, RepositoryUpdateInterface, RepositoryDeleteInterface
{
    use RepositoryCriteriaTrait, RepositoryQueryTrait, RepositoryTransactionsTrait, MutationHelperTrait;
    /**
     * Instance of laravel App.
     *
     * @var App
     */
    protected $app;

    /**
     * Instance of the model.
     *
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * Class name of the Eloquent model.
     *
     * @var string
     */
    protected $model;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $instance;

    /**
     * Create instance of a repository.
     *
     * @param \Illuminate\Container\Container $app
     * @param \Illuminate\Support\Collection  $collection
     */
    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->refresh();
        $this->boot();
    }

    /**
     * Create a new instance of repository.
     *
     * @return $this
     */
    public function self()
    {
        return $this->makeRepository(static::class);
    }

    /**
     * Create an instance of given repository.
     *
     * @param string $class
     *
     * @return Repository
     */
    public function makeRepository(string $class)
    {
        return $this->app->make($class);
    }

    /**
     * Get empty query for the Eloquent model.
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     *
     * @return $this
     */
    public function refresh()
    {
        $this->query = $this->getModel()->newQuery();

        return $this;
    }

    /**
     * Reset repository scope.
     *
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);

        return $this;
    }

    /**
     * Create an instance of repository's model.
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        if (! $this->instance) {
            $model = $this->app->make($this->getModelClass());

            if (! $model instanceof Model) {
                throw new RepositoryException($this->getModelClass());
            }

            $this->instance = $model;
        }

        return $this->instance;
    }

    public function getModelClass()
    {
        if ($this->model) {
            return $this->model;
        }

        // Backward compatible.
        if (method_exists($this, 'model')) {
            return $this->model();
        }

        throw new RepositoryException('$model property not defined on '.get_class($this));
    }

    /**
     * Booting repository.
     *
     * @return void
     */
    protected function boot()
    {
        $class = static::class;
        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'boot'.class_basename($trait))) {
                call_user_func([$this, $method]);
            }
        }
    }
}
