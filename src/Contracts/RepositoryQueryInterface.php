<?php namespace Znck\Repositories\Contracts;

interface RepositoryQueryInterface
{
    /**
     * Get all results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all();

     /**
      * Get number of results.
      *
      * @return int
      */
     public function count();

    /**
     * Get all results paginated.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 50);

    /**
     * Get result with matching id.
     *
     * @param string|int $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id);

    /**
     * Get all results with the field-value constraint.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findBy($field, $value);

    /**
     * Get all results with given constraints.
     *
     * @param array $condition
     *
     * @return \Illuminate\Support\Collection
     */
    public function where(array $condition);
}
