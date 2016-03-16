<?php namespace Znck\Repositories\Contracts;

interface RepositoryDeleteInterface
{
    /**
     * Delete resource.
     *
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     * @return bool
     */
    public function delete($id);
}
