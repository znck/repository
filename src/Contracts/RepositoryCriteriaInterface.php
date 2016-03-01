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
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criterion
     *
     * @return $this
     */
    public function getByCriteria(CriteriaInterface $criterion);

    /**
     * Push a criterion on the repository's criteria list.
     *
     * @param \Znck\Repositories\Contracts\CriteriaInterface $criterion
     *
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criterion);

    /**
     * Apply all criteria.
     *
     * @return $this
     */
    public function applyCriteria();
}
