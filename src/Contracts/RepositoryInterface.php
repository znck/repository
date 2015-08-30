<?php namespace Znck\Repositories\Contracts;

/**
 * Interface RepositoryInterface
 *
 * @package Znck\Repositories\Contracts
 */
interface RepositoryInterface
{
    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * @param int   $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*']);

    /**
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*']);

    /**
     * @param array $condition
     * @param array $columns
     *
     * @return mixed
     */
    public function where(array $condition, $columns = ['*']);
}