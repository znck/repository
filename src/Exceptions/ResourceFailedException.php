<?php namespace Znck\Repositories\Exceptions;

use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Znck\Repositories\Contracts\ErrorMessageProvider;

class ResourceFailedException extends HttpException implements ErrorMessageProvider
{
    protected $errors;

    public function __construct($message = null, $errors = null, \Exception $previous = null, array $headers = [], $code = 0)
    {
        if (is_null($errors)) {
            $this->errors = new MessageBag();
        } else {
            $this->errors = is_array($errors) ? new MessageBag($errors) : $errors;
        }
        parent::__construct(422, $message, $previous, $headers, $code);
    }

    /**
     * Get the errors message bag.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getErrors() : MessageBag
    {
        return $this->errors;
    }

    /**
     * Determine if message bag has any errors.
     *
     * @return bool
     */
    public function hasErrors() : bool
    {
        return ! $this->errors->isEmpty();
    }
}
