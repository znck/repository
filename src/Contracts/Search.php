<?php namespace Znck\Repositories\Contracts;

/**
 * Interface Search.
 *
 * @internal Znck\Repositories\Contracts
 */
interface Search extends Query
{
    /**
     * Find models matching query string.
     *
     * @param string $q
     *
     * @param callable|\Closure $callback
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $q, $callback = null);
}
