<?php namespace Znck\Repositories\Contracts;

use Illuminate\Contracts\Support\MessageBag;

interface ErrorMessageProvider
{
    public function hasErrors() : bool;

    public function getErrors() : MessageBag;
}
