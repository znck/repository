<?php namespace Znck\Repositories\Contracts;

interface RepositoryUpdateInterface
{
    /**
     * Update resource with given attributes.
     *
     * @param array $attributes
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id);
}
