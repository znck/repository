<?php namespace Znck\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Repository extends Search
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
     * @param array $attributes
     * @param array $options
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

    /**
     * Validate attributes.
     *
     * @param array $attributes
     * @param Model $model
     *
     * @return $this
     */
    public function validate(array $attributes, Model $model = null);

    /**
     * Skip validation.
     *
     * @param bool $skip
     *
     * @return $this
     */
    public function skipValidation($skip = true);

    /**
     * Reset repository.
     *
     * @return $this
     */
    public function refresh();

    /**
     * Get underlying eloquent model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel() : Model;

    /**
     * Use relation for query.
     *
     * @param string $relation
     *
     * @return $this
     */
    public function useRelation(string $relation);
}
