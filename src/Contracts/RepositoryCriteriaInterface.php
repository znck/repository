<?php

namespace Znck\Repositories\Contracts;

interface RepositoryCriteriaInterface
{
    /**
     * Set whether to use criteria or not.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipCriteria($status = true);

    /**
     * Collection of criterion on the repository.
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function getCriteria();

    /**
     * Apply criterion without pushing on the repository's criteria list.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criteria
     *
     * @return $this
     */
    public function getByCriteria(CriteriaInterface $criteria);

    /**
     * Push a criterion on the repository's criteria list.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criteria
     *
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criteria);

    /**
     * Apply all criteria.
     *
     * @return $this
     */
    public function applyCriteria();
}
