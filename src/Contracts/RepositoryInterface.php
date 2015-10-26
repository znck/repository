<?php

namespace Znck\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * Set fields for queries.
     *
     * @param array $columns
     * @param bool $merge
     *
     * @return $this
     */
    public function setFields(array $columns, $merge = true);

    /**
     * Get all results.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all(array $columns = []);

    /**
     * Get all results paginated.
     *
     * @param int $perPage
     * @param array|null $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 50, array $columns = []);

    /**
     * Get result with matching id.
     *
     * @param string|int $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, array $columns = []);

    /**
     * Get all results with the field-value constraint.
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, array $columns = []);

    /**
     * Get all results with given constraints.
     *
     * @param array $condition
     * @param array $columns
     *
     * @return mixed
     */
    public function where(array $condition, array $columns = []);
}
