<?php namespace Znck\Repositories\Traits;

trait MutationHelperTrait
{
    /**
     * Create resource with given attributes.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes) {
        $model = $this->app->make($this->model);

        return $this->transaction(
            function () use ($model, $attributes) {
                return $this->creating($model, $attributes);
            },
            $model
        );
    }

    /**
     * Update resource with given attributes.
     *
     * @param array $attributes
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id) {
        $model = $this->parseSelf($id);

        return $this->transaction(
            function () use ($model, $attributes) {
                return $this->updating($model, $attributes);
            },
            $model
        );
    }

    /**
     * Delete resource.
     *
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     *
     * @return bool
     */
    public function delete($id) {
        $model = $this->parseSelf($id);

        return $this->transaction(
            function () use ($model) {
                return $this->deleting($model);
            },
            $model
        );
    }
}
