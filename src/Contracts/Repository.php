<?php namespace Znck\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Repository extends Search
{
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
}
