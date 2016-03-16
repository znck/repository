<?php namespace Znck\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Znck\Repositories\Contracts\CriteriaInterface;
use Znck\Repositories\Contracts\RepositoryCreateInterface;
use Znck\Repositories\Contracts\RepositoryCriteriaInterface;
use Znck\Repositories\Contracts\RepositoryDeleteInterface;
use Znck\Repositories\Contracts\RepositoryQueryInterface;
use Znck\Repositories\Contracts\RepositoryUpdateInterface;
use Znck\Repositories\Exceptions\RepositoryException;

abstract class Repository implements RepositoryQueryInterface, RepositoryCriteriaInterface, RepositoryCreateInterface, RepositoryUpdateInterface, RepositoryDeleteInterface
{
    /**
     * Instance of laravel App.
     *
     * @var App
     */
    protected $app;

    /**
     * Collection of criterion.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $criteria;

    /**
     * Instance of the model.
     *
     * @var \Illuminate\Database\Eloquent\Builder
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
     * @param \Illuminate\Support\Collection $collection
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
     * Reset repository scope.
     *
     * @return $this
     */
    protected function resetScope()
    {
        $this->skipCriteria(false);

        return $this;
    }

    /**
     * Get empty query for the Eloquent model.
     *
     * @throws \Znck\Repositories\Exceptions\RepositoryException
     *
     * @return $this
     */
    protected function refresh()
    {
        $this->model = $this->makeModel()->newQuery();

        return $this;
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

        if (! $model instanceof Model) {
            throw new RepositoryException($this->model());
        }

        return $model;
    }

    /**
     * Class name of the Eloquent model.
     *
     * @return string
     */
    abstract protected function model();

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
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criterion
     *
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criterion)
    {
        $this->criteria->push($criterion);

        return $this;
    }

    /**
     * Apply a criterion without pushing it.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criterion
     *
     * @return $this
     */
    public function getByCriteria(CriteriaInterface $criterion)
    {
        $this->model = $criterion->apply($this->model, $this);

        return $this;
    }

    /**
     * Get all results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        $this->applyCriteria();

        return $this->model->get();
    }

    /**
     * Get result with matching id.
     *
     * @param string|int $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id)
    {
        $this->applyCriteria();

        return $this->model->find($id);
    }

    /**
     * Get all results with the field-value constraint.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findBy($field, $value)
    {
        $this->applyCriteria();

        return $this->model->where($field, '=', $value)->first();
    }

    /**
     * Get all results paginated.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 20)
    {
        $this->applyCriteria();

        return $this->model->paginate($perPage);
    }

    /**
     * Get all results with given constraints.
     *
     * @param array $condition
     *
     * @return \Illuminate\Support\Collection
     */
    public function where(array $condition)
    {
        $this->applyCriteria();

        $count = count($condition);

        if ($count < 2) {
            throw new \InvalidArgumentException();
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

        return $this->model->where($first, $second, $third, $fourth)->get();
    }
}
