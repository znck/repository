<?php

namespace Znck\Repositories\Contracts;

interface CriteriaInterface
{
    /**
     * Apply the criteria on the repository.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Znck\Repositories\Repository $repository
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $repository);
}
