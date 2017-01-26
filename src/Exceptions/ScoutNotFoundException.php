<?php namespace Znck\Repositories\Exceptions;

use Exception;
use Laravel\Scout\Searchable;

class ScoutNotFoundException extends Exception
{
    public function __construct(string $model)
    {
        parent::__construct("{$model} should use ".Searchable::class);
    }
}
