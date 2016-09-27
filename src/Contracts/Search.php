<?php namespace Znck\Repositories\Contracts;

/**
 * Interface Search
 * @internal Znck\Repositories\Contracts
 */
interface Search extends Query
{
    /**
     * Find models matching query string.
     *
     * @param string $q
     *
     * @return \Laravel\Scout\Builder
     */
    public function search(string $q);
}
