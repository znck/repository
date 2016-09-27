<?php namespace Znck\Repositories\Exceptions;

use Exception;
use Illuminate\Contracts\Support\MessageBag as MessageBagInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Znck\Repositories\Contracts\ErrorMessageProvider;

class ResourceException extends HttpException implements ErrorMessageProvider
{
    /**
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * ResourceException constructor.
     *
     * @param null            $message
     * @param null            $errors
     * @param \Exception|null $previous
     * @param array           $headers
     * @param int             $code
     */
    public function __construct(
        $message = null,
        $errors = null,
        Exception $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(422, $message, $previous, $headers, $code);

        if ($errors instanceof MessageBagInterface) {
            $this->errors = $errors;
        } elseif (is_array($errors)) {
            $this->errors = new MessageBag($errors);
        } elseif ($errors instanceof Collection) {
            $this->errors = new MessageBag($errors->all());
        } else {
            $this->errors = new MessageBag();
        }
    }

    public function getErrors() : MessageBagInterface
    {
        return $this->errors;
    }

    public function hasErrors() : bool
    {
        return ! $this->getErrors()->isEmpty();
    }
}
