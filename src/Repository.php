<?php namespace Znck\Repositories;

/**
 * This file belongs to znck.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Znck\Repositories\Contracts\CriteriaInterface;
use Znck\Repositories\Contracts\RepositoryCriteriaInterface;
use Znck\Repositories\Contracts\RepositoryInterface;
use Znck\Repositories\Exceptions\RepositoryException;

/**
 * Class Repository
 *
 * @package Znck\Repositories
 */
abstract class Repository implements RepositoryInterface, RepositoryCriteriaInterface
{
    /**
     * @type App
     */
    private $app;

    /**
     * @type \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;

    /**
     * @type \Illuminate\Support\Collection
     */
    protected $criteria;

    /**
     * @type bool
     */
    protected $skipCriteria;

    /**
     * Create instance of repository
     *
     * @param \Illuminate\Container\Container $app
     *
     * @param \Illuminate\Support\Collection  $collection
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->refreshModel();
    }

    /**
     * Class name of Eloquent model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Get empty query for the Eloquent model
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     */
    public function refreshModel()
    {
        $model = $this->makeModel();

        return $this->model = $model->newQuery();
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @param int   $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();

        return $this->model->find($id, $columns);
    }

    /**
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, '=', $value)->first($columns);
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);

        return $this;
    }

    /**
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
     * @return \Illuminate\Support\Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
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

    /**
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
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     */
    protected function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * @param array $condition
     * @param array $columns
     *
     * @return mixed
     */
    public function where(array $condition, $columns = ['*'])
    {
        $count = count($condition);
        assert($count > 1);

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
}