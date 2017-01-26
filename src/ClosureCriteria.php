<?php namespace Znck\Repositories;

use Closure;
use Znck\Repositories\Contracts\Criteria;
use Znck\Repositories\Contracts\Repository as RepositoryContract;

class ClosureCriteria implements Criteria
{
    protected $callable;

    /**
     * ClosureCriteria constructor.
     *
     * @param Closure $callable
     */
    public function __construct(Closure $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Apply criteria in query repository.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Laravel\Scout\Builder $model
     * @param RepositoryContract                                                                              $repository
     *
     * @return mixed|void
     */
    public function apply($model, RepositoryContract $repository)
    {
        $callback = $this->callable;

        $callback($model, $repository);
    }
}
