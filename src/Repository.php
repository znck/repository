<?php namespace Znck\Repositories;

use Closure;
use Exception;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\Searchable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Znck\Repositories\Contracts\Criteria;
use Znck\Repositories\Exceptions\RepositoryException;
use Znck\Repositories\Exceptions\UnsupportedScoutFeature;

abstract class Repository implements Contracts\Repository
{
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
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validator;

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

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

    /**
     * @var bool
     */
    protected $skipValidation = false;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->boot();
    }

    public function boot() {
    }

    protected function makeModel() {
        $class = $this->model;

        if (!is_string($class) or !class_exists($class)) {
            throw new RepositoryException($class);
        }

        $this->instance = $this->app->make($class);

        if (!$this->instance instanceof Model) {
            throw new RepositoryException($class);
        }

        $this->query = $this->instance->newQuery();
    }

    public static function register(array $map) {
        self::$repositories += $map;
    }

    protected function isSearching() {
        return !is_null($this->scout);
    }

    protected function applyCriteria() {
        $this->getQuery()->with($this->with);

        if ($this->skipCriteria) {
            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            $criteria->apply($this->getQuery(), $this);
        }

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Get all criteria.
     *
     * @return \Illuminate\Support\Collection|Criteria[]
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * Remove all criteria.
     *
     * @return $this
     */
    public function clearCriteria() {
        $this->criteria = new Collection();

        return $this;
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     *
     * @return int
     * @throws \Znck\Repositories\Exceptions\UnsupportedScoutFeature
     */
    public function count($columns = '*') {
        if ($this->isSearching()) {
            throw new UnsupportedScoutFeature('scout: count() is not supported!');
        }

        $this->applyCriteria();

        return $this->getQuery()->count($columns);
    }

    /**
     * Get all items.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*']) {
        if ($this->isSearching()) {
            return $this->scout->get()->load($this->with);
        }

        $this->applyCriteria();

        return $this->getQuery()->get($columns);
    }

    /**
     * Find a model by its primary key.
     *
     * @param string|int $id
     * @param array $columns
     *
     * @return Model
     */
    public function find($id, $columns = ['*']) {
        if ($this->isSearching()) {
            return $this->scout->where($this->getModel()->getKeyName(), $id)->first()->load($this->with);
        }

        $this->applyCriteria();

        $result = $this->getQuery()->find($id, $columns);

        if (!$result instanceof Model) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Find a model by given key. (This would return first matching object).
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Model
     */
    public function findBy(string $key, $value) {
        if ($this->isSearching()) {
            return $this->scout->where($key, $value)->get()->load($this->with);
        }

        $this->applyCriteria();

        $result = $this->getQuery()->where($key, $value)->first();

        if (!$result instanceof Model) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    /**
     * Find models by their primary keys.
     *
     * @param array $ids
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Znck\Repositories\Exceptions\UnsupportedScoutFeature
     */
    public function findMany(array $ids, $columns = ['*']) {
        if ($this->isSearching()) {
            throw new UnsupportedScoutFeature('scout: wherein is not supported!');
        }
        $this->applyCriteria();

        return $this->getQuery()
            ->whereIn($this->getModel()->getKeyName(), $ids)
            ->get();
    }

    /**
     * @return Model
     */
    public function getModel(): Model {
        return $this->instance;
    }

    /**
     * Get all items.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function first($columns = ['*']) {
        if ($this->isSearching()) {
            return $this->scout->first()->load($this->with);
        }

        $this->applyCriteria();

        return $this->getQuery()->first($columns);
    }

    /**
     * Paginate the given query.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
        if ($this->isSearching()) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator = $this->scout->paginate($perPage, $columns, $perPage);
            /** @var \Illuminate\Database\Eloquent\Collection $collection */
            $collection = $paginator->getCollection();
            $collection->load($this->with);

            return $paginator;
        }
        $this->applyCriteria();

        return $this->getQuery()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
        if ($this->isSearching()) {
            /** @var \Illuminate\Pagination\Paginator $paginator */
            $paginator = $this->scout->simplePaginate($perPage, $columns, $perPage);
            /** @var \Illuminate\Database\Eloquent\Collection $collection */
            $collection = $paginator->getCollection();
            $collection->load($this->with);

            return $paginator;
        }
        $this->applyCriteria();

        return $this->getQuery()->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Find models matching query string.
     *
     * @param string $q
     * @param callable|\Closure $callback
     *
     * @return $this
     * @throws \Exception
     */
    public function search(string $q, $callback = null) {
        if (!in_array(Searchable::class, class_uses_recursive($this->model))) {
            throw new Exception("{$this->model} should use ".Searchable::class);
        }

        /* @noinspection PhpUndefinedMethodInspection */
        $this->scout = $this->getModel()->search($q);

        if (!$this->skipCriteria) {
            foreach ($this->getCriteria() as $criteria) {
                $criteria->apply($this->scout, $this);
            }
        }

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
    public function popCriteria(Criteria $criteria = null) : Criteria {
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
    public function pushCriteria(Criteria $criteria) {
        $this->getCriteria()->push($criteria);

        return $this;
    }

    /**
     * Reset repository.
     *
     * @return \Znck\Repositories\Contracts\Repository
     */
    public function refresh() {
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
    public function skipCriteria($skip = true) {
        $this->skipCriteria = $skip;

        return $this;
    }

    /**
     * Skip validation.
     *
     * @param bool $skip
     *
     * @return \Znck\Repositories\Contracts\Repository
     */
    public function skipValidation($skip = true) {
        $this->skipValidation = $skip;

        return $this;
    }

    /**
     * Validate attributes.
     *
     * @param array $attributes
     * @param Model $model
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return $this
     */
    public function validate(array $attributes, Model $model = null) {
        if ($this->skipValidation) {
            return $this;
        }

        if (!$this->validator) {
            $this->validator = $this->app->make(Factory::class);
        }

        $validator = $this->validator->make(
            $this->prepareAttributes($attributes),
            $this->getRules($attributes, $model)
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }

    public function prepareAttributes(array $attributes) {
        return $attributes;
    }

    /**
     *
     * @param array $attributes
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getRules(array $attributes, Model $model = null): array {
        if (is_null($model)) {
            return $this->getCreateRules($attributes);
        }

        return $this->getUpdateRules($this->getCreateRules($attributes), $attributes, $model);
    }

    public function getCreateRules(array $attributes) {
        return $this->rules;
    }

    /**
     * @param array $rules
     * @param array $attributes
     * @param Model $model
     *
     * @return array
     */
    public function getUpdateRules(array $rules, array $attributes, $model) {
        return array_only($rules, array_keys($attributes));
    }

    /**
     * Get result of the query.
     *
     * @param string|array|\Closure $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and') {
        $this->applyCriteria();

        return $this->getQuery()->where($column, $operator, $value, $boolean)->get();
    }

    public function with($relations) {
        $this->with = array_merge($this->with, (is_array($relations) ? $relations : (array)$relations));

        return $this;
    }
}
