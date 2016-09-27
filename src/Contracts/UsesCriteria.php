<?php namespace Znck\Repositories\Contracts;

/**
 * Interface UsesCriteria
 * @internal Znck\Repositories\Contracts
 */
interface UsesCriteria
{
    /**
     * Push Criteria for filter the query
     *
     * @param Criteria $criteria
     *
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * Remove a criterion
     *
     * @param Criteria|null $criteria
     *
     * @return Criteria
     */
    public function popCriteria(Criteria $criteria = null) : Criteria;

    /**
     * Remove all criteria.
     *
     * @return $this
     */
    public function clearCriteria();

    /**
     * @param bool $skip
     *
     * @return $this
     */
    public function skipCriteria($skip = true);

    /**
     * Get all criteria.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCriteria();
}
