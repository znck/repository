<?php namespace Znck\Repositories\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class RepositoryException extends Exception
{
    public function __construct($class, $code = 0, Exception $previous = null)
    {
        parent::__construct("{$class} must be an instance of ".Model::class, $code, $previous);
    }
}
