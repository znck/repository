<?php namespace Znck\Repositories\Contracts;

/**
 * This file belongs to repositories.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */
/**
 * Interface CriteriaInterface
 *
 * @package Znck\Repositories\Contracts
 */
interface CriteriaInterface
{
    /**
     * @param \Illuminate\Database\Eloquent\Model              $model
     * @param \Znck\Repositories\Contracts\RepositoryInterface $repository
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, RepositoryInterface $repository);
}