<?php namespace Znck\Repositories;

use Closure;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use Znck\Repositories\Contracts\Criteria;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Znck\Repositories\Exceptions\RepositoryException;
use Znck\Repositories\Exceptions\ScoutNotFoundException;
use Znck\Repositories\Exceptions\UnsupportedScoutFeature;

abstract class Repository implements Contracts\Repository, Contracts\Validating, Contracts\HasTransactions, Contracts\RepositoryExtras
{
    use Traits\RepositoryHelper;

    public static $repositories = [];

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $criteria;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var Model
     */
    protected $instance;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var \Laravel\Scout\Builder
     */
    protected $scout;

    /**
     * Eager relations.
     *
     * @var array
     */
    protected $with = [];

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->boot();
    }

    public function boot()
    {
    }

    protected function makeModel()
    {
        $class = $this->model;

        if (! is_string($class) or ! class_exists($class)) {
            throw new RepositoryException($class);
        }

        $this->instance = $this->app->make($class);

        if (! $this->instance instanceof Model) {
            throw new RepositoryException($class);
        }

        $this->query = $this->instance->newQuery();
    }

    public static function register(array $map)
    {
        self::$repositories += $map;
    }

    protected function isSearching()
    {
        return ! is_null($this->scout);
    }

    protected function applyCriteria()
    {
        $this->getQuery()->with($this->with);

        if ($this->skipCriteria) {
            return $this;
        }

        if ($this->isSearching()) {
            foreach ($this->getCriteria() as $criteria) {
                $criteria->apply($this->scout, $this);
            }
        } else {
            foreach ($this->getCriteria() as $criteria) {
                $criteria->apply($this->getQuery(), $this);
            }
        }

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        if (is_null($this->query)) {
            $this->makeModel();
        }

        return $this->query;
    }

    /**
     * Get all criteria.
     *
     * @return \Illuminate\Support\Collection|Criteria[]
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Remove all criteria.
     *
     * @return $this
     */
    public function clearCriteria()
    {
        $this->criteria = new Collection();

        return $this;
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     *
     * @throws \Znck\Repositories\Exceptions\UnsupportedScoutFeature
     *
     * @return int
     */
    public function count($columns = '*')
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            throw new UnsupportedScoutFeature('scout: count() is not supported!');
        }

        return $this->getQuery()->count($columns);
    }

    /**
     * Get all items.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            return $this->scout->get()->load($this->with);
        }

        return $this->getQuery()->get($columns);
    }

    /**
     * Find a model by its primary key.
     *
     * @param string|int $id
     * @param array      $columns
     *
     * @return Model
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            return $this->scout->where($this->getModel()->getKeyName(), $id)->first()->load($this->with);
        }

        $result = $this->getQuery()->find($id, $columns);

        if (! $result instanceof Model) {
            throw new NotFoundResourceException('Not found.');
        }

        return $result;
    }

    /**
     * Find a model by given key. (This would return first matching object).
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Model
     */
    public function findBy(string $key, $value)
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            return $this->scout->where($key, $value)->get()->load($this->with);
        }

        $result = $this->getQuery()->where($key, $value)->first();

        if (! $result instanceof Model) {
            throw new NotFoundResourceException('Not found.');
        }

        return $result;
    }

    /**
     * Find models by their primary keys.
     *
     * @param array $ids
     * @param array $columns
     *
     * @throws \Znck\Repositories\Exceptions\UnsupportedScoutFeature
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany(array $ids, $columns = ['*'])
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            throw new UnsupportedScoutFeature('scout: wherein is not supported!');
        }

        return $this->getQuery()
                    ->whereIn($this->getModel()->getKeyName(), $ids)
                    ->get();
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        if (is_null($this->instance)) {
            $this->makeModel();
        }

        return $this->instance;
    }

    /**
     * Get all items.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function first($columns = ['*'])
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            return $this->scout->first()->load($this->with);
        }

        return $this->getQuery()->first($columns);
    }

    /**
     * Paginate the given query.
     *
     * @param int      $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator = $this->scout->paginate($perPage, $pageName, $perPage);
            /** @var \Illuminate\Database\Eloquent\Collection $collection */
            $collection = $paginator->getCollection();
            $collection->load($this->with);

            return $paginator;
        }

        return $this->getQuery()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int      $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        if ($this->isSearching()) {
            /** @var \Illuminate\Pagination\Paginator $paginator */
            $paginator = $this->scout->paginate($perPage, $pageName, $perPage);
            /** @var \Illuminate\Database\Eloquent\Collection $collection */
            $collection = $paginator->getCollection();
            $collection->load($this->with);

            return $paginator;
        }

        return $this->getQuery()->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Find models matching query string.
     *
     * @param string            $q
     * @param callable|\Closure $callback
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function search(string $q, $callback = null)
    {
        if (! in_array(Searchable::class, class_uses_recursive($this->model))) {
            throw new ScoutNotFoundException($this->model);
        }

        /* @noinspection PhpUndefinedMethodInspection */
        $this->scout = $this->getModel()->search($q);

        if (is_callable($callback)) {
            call_user_func_array($callback, [$this->scout, $this]);
        } elseif ($callback instanceof Closure) {
            $callback($this->scout, $this);
        }

        return $this;
    }

    /**
     * Remove a criterion.
     *
     * @param Criteria|null $criteria
     *
     * @return Criteria
     */
    public function popCriteria(Criteria $criteria = null) : Criteria
    {
        if (is_null($criteria)) {
            return $this->getCriteria()->pop();
        }

        $class = get_class($criteria);
        foreach ($this->getCriteria() as $index => $value) {
            if ($value instanceof $class) {
                $this->getCriteria()->slice($index, 1);

                return $value;
            }
        }

        return null;
    }

    /**
     * Push Criteria for filter the query.
     *
     * @param Criteria $criteria
     *
     * @return $this
     */
    public function pushCriteria(Criteria $criteria)
    {
        $this->getCriteria()->push($criteria);

        return $this;
    }

    /**
     * Reset repository.
     *
     * @return \Znck\Repositories\Contracts\Repository
     */
    public function refresh()
    {
        $this->query = $this->getModel()->newQuery();
        $this->scout = null;
        $this->boot();

        return $this;
    }

    /**
     * @param bool $skip
     *
     * @return $this
     */
    public function skipCriteria($skip = true)
    {
        $this->skipCriteria = $skip;

        return $this;
    }

    /**
     * Get result of the query.
     *
     * @param string|array|\Closure $column
     * @param string                $operator
     * @param mixed                 $value
     * @param string                $boolean
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->applyCriteria();

        return $this->getQuery()->where($column, $operator, $value, $boolean)->get();
    }

    public function with($relations)
    {
        $this->with = array_merge($this->with, (is_array($relations) ? $relations : (array) $relations));

        return $this;
    }

    public function __call($name, $arguments)
    {
        $query = $this->getQuery();
        $result = call_user_func_array([$query, $name], $arguments);

        if ($query === $result) {
            return $this;
        }

        return $result;
    }
}
