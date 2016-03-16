<?php namespace Znck\Repositories\Contracts;

interface RepositoryCreateInterface
{
    /**
     * Create resource with given attributes.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes);
}
