<?php namespace Znck\Repositories\Contracts;

interface Criteria
{
    /**
     * Apply criteria in query repository.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Laravel\Scout\Builder $model
     * @param Repository                                                                                      $repository
     *
     * @return mixed|void
     */
    public function apply($model, Repository $repository);
}
