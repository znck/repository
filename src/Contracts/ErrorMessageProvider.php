<?php namespace Znck\Repositories\Contracts;

use Illuminate\Contracts\Support\MessageBag;

interface ErrorMessageProvider
{
    /**
     * Check if it has errors.
     *
     * @return bool
     */
    public function hasErrors() : bool;

    /**
     * Get message bag.
     *
     * @return \Illuminate\Contracts\Support\MessageBag
     */
    public function getErrors() : MessageBag;
}
