<?php

namespace Znck\Repositories\Contracts;

interface CriteriaInterface
{
    /**
     * Apply the criteria on the repository.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $model
     * @param \Znck\Repositories\Contracts\RepositoryQueryInterface                         $repository
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, RepositoryQueryInterface $repository);
}
