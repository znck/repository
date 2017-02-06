<?php

namespace Znck\Repositories\Contracts;

interface RepositoryExtras
{
    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update the model in the database.
     *
     * @param Model|string|int $id
     * @param array            $attributes
     * @param array            $options
     *
     * @return Model
     */
    public function update($id, array $attributes, array $options = []);

    /**
     * Delete the model from the database.
     *
     * @param Model|string|int $id
     *
     * @return bool|null
     */
    public function delete($id);
}
