<?php namespace Znck\Repositories\Contracts;

/**
 * Interface Query
 *
 * @internal Znck\Repositories\Contracts
 */
interface Query extends UsesCriteria
{
    /**
     * Get all items.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*']);
    /**
     * Find a model by its primary key.
     *
     * @param string|int $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, $columns = ['*']);

    /**
     * Find a model by given key. (This would return first matching object).
     *
     * @param string $key
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findBy(string $key, $value);


    /**
     * Find models by their primary keys.
     *
     * @param array $ids
     * @param array $columns
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany(array $ids, $columns = ['*']);


    /**
     * Paginate the given query.
     *
     * @param  int $perPage
     * @param  array $columns
     * @param  string $pageName
     * @param  int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int $perPage
     * @param  array $columns
     * @param  string $pageName
     * @param  int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string $columns
     *
     * @return int
     */
    public function count($columns = '*');

    /**
     * Get result of the query.
     *
     * @param string|array|\Closure $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and');
}
