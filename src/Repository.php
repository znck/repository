<?php

namespace Znck\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Znck\Repositories\Contracts\CriteriaInterface;
use Znck\Repositories\Contracts\RepositoryCriteriaInterface;
use Znck\Repositories\Contracts\RepositoryInterface;
use Znck\Repositories\Exceptions\RepositoryException;

abstract class Repository implements RepositoryInterface, RepositoryCriteriaInterface
{
    /**
     * Instance of laravel App.
     *
     * @var App
     */
    protected $app;

    /**
     * Select columns for query.
     *
     * @var array
     */
    protected $columns = ['*'];

    /**
     * Collection of criterion.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $criteria;

    /**
     * Instance of the model.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;

    /**
     * Controls whether to use criteria or not.
     *
     * @var bool
     */
    protected $skipCriteria;

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
        $this->refreshModel();
        $this->boot();
    }

    /**
     * Reset repository scope.
     *
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        $this->setFields(['*'], false);

        return $this;
    }

    /**
     * Get empty query for the Eloquent model.
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function refreshModel()
    {
        $model = $this->makeModel();

        return $this->model = $model->newQuery();
    }

    /**
     * Booting repository.
     *
     * @return void
     */
    protected function boot()
    {
        // Extend it in child.
    }

    /**
     * Create an instance of repository's model.
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException('Class '.$this->model().' must be an instance of Illuminate\\Database\\Eloquent\\Model');
        }

        return $model;
    }

    /**
     * Class name of the Eloquent model.
     *
     * @return string
     */
    abstract public function model();

    /**
     * Set fields for queries.
     *
     * @param array $columns
     * @param bool  $merge
     *
     * @return $this
     */
    public function setFields(array $columns, $merge = true)
    {
        if ($merge) {
            $this->columns = array_merge($this->columns, $columns);
        } else {
            $this->columns = $columns;
        }

        return $this;
    }

    /**
     * Get all results.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all(array $columns = [])
    {
        $columns = array_merge($this->columns, $columns);

        return $this->model->get($columns);
    }

    /**
     * Get result with matching id.
     *
     * @param string|int $id
     * @param array      $columns
     *
     * @return mixed
     */
    public function find($id, array $columns = [])
    {
        $columns = array_merge($this->columns, $columns);

        return $this->model->find($id, $columns);
    }

    /**
     * Get all results with the field-value constraint.
     *
     * @param string $field
     * @param mixed  $value
     * @param array  $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, array $columns = [])
    {
        $columns = array_merge($this->columns, $columns);

        return $this->model->where($field, '=', $value)->first($columns);
    }

    /**
     * Get all results paginated.
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 15, array $columns = [])
    {
        $columns = array_merge($this->columns, $columns);

        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Get all results with given constraints.
     *
     * @param array $condition
     * @param array $columns
     *
     * @return mixed
     */
    public function where(array $condition, array $columns = [])
    {
        $columns = array_merge($this->columns, $columns);
        $count = count($condition);

        if ($count < 2) {
            return $this->model->get($columns);
        }

        $third = null;
        $fourth = 'and';
        if (count($condition) == 4) {
            list($first, $second, $third, $fourth) = $condition;
        } elseif ($count == 3) {
            list($first, $second, $third) = $condition;
        } else {
            list($first, $second) = $condition;
        }

        return $this->model->where($first, $second, $third, $fourth)->get($columns);
    }

    /**
     * Apply all criteria.
     *
     * @return $this
     */
    public function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof CriteriaInterface) {
                $this->model = $criteria->apply($this->model, $this);
            }
        }

        return $this;
    }

    /**
     * Set whether to use criteria or not.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;

        return $this;
    }

    /**
     * Collection of criterion on the repository.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Push a criterion on the repository's criteria list.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criteria
     *
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criteria)
    {
        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Apply a criterion without pushing it.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criteria
     *
     * @return $this
     */
    public function getByCriteria(CriteriaInterface $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);

        return $this;
    }
}
