<?php namespace Znck\Repositories\Contracts;

/**
 * This file belongs to repositories.
 *
 * Author: Rahul Kadyan, <hi@znck.me>
 * Find license in root directory of this project.
 */

interface RepositoryCriteriaInterface
{
    public function skipCriteria($status = true);

    public function getCriteria();

    public function getByCriteria(CriteriaInterface $criteria);

    public function pushCriteria(CriteriaInterface $criteria);
}