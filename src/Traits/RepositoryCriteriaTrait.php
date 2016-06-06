<?php namespace Znck\Repositories\Traits;

use Znck\Repositories\Contracts\CriteriaInterface;

/**
 * @internal
 */
trait RepositoryCriteriaTrait
{
    /**
   * Collection of criterion.
   *
   * @var \Illuminate\Support\Collection
   */
  protected $criteria;

  /**
   * Controls whether to use criteria or not.
   *
   * @var bool
   */
  protected $skipCriteria;

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
              $this->query = $criteria->apply($this->query, $this);
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
      $this->query = $criterion->apply($this->query, $this);

      return $this;
  }
}
