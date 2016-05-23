<?php namespace Znck\Repositories\Traits;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RepositoryQueryTrait
{
    /**
    * Throw error if resource not found.
    *
    * @var bool
    */
   protected $throwError = false;

   /**
    * Throw HTTP not found error if resource not found.
    *
    * @return $this
    */
   public function enableHttpMode()
   {
       $this->throwError = true;

       return $this;
   }

   /**
    * Don't throw error if resource not found.
    *
    * @return $this
    */
   public function disableHttpMode()
   {
       $this->throwError = false;

       return $this;
   }

  /**
   * Get all results.
   *
   * @return \Illuminate\Support\Collection
   */
  public function all()
  {
      $this->applyCriteria();

      return $this->query->get();
  }

   /**
    * Get number of results.
    *
    * @return int
    */
   public function count()
   {
       $this->applyCriteria();

       return $this->query->count();
   }

  /**
   * Get result with matching id.
   *
   * @param string|int $id
   *
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function find($id)
  {
      $this->applyCriteria();

      $item = $this->query->find($id);

      if ($this->throwError and ! $item) {
          throw new NotFoundHttpException();
      }

      return $item;
  }

  /**
   * Get all results with the field-value constraint.
   *
   * @param string $field
   * @param mixed $value
   *
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function findBy($field, $value)
  {
      $this->applyCriteria();

      $item = $this->query->where($field, '=', $value)->first();
      if ($this->throwError and ! $item) {
          throw new NotFoundHttpException();
      }

      return $item;
  }

  /**
   * Get all results paginated.
   *
   * @param int $perPage
   *
   * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
   */
  public function paginate(int $perPage = 20)
  {
      $this->applyCriteria();

      return $this->query->paginate($perPage);
  }

  /**
   * Get all results with given constraints.
   *
   * @param array $condition
   *
   * @return \Illuminate\Support\Collection
   */
  public function where(array $condition)
  {
      $this->applyCriteria();

      $second = '=';
      $fourth = 'and';
      $count = count($condition);
      if ($count === 4) {
          list($first, $second, $third, $fourth) = $condition;
      } elseif ($count === 3) {
          list($first, $second, $third) = $condition;
      } elseif ($count === 2) {
          list($first, $third) = $condition;
      } else {
          throw new InvalidArgumentException();
      }

      return $this->query->where($first, $second, $third, $fourth)->get();
  }
}
