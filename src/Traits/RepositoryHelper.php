<?php

namespace Znck\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Znck\Repositories\Exceptions\DeleteResourceException;
use Znck\Repositories\Exceptions\NotFoundResourceException;
use Znck\Repositories\Exceptions\StoreResourceException;
use Znck\Repositories\Exceptions\UpdateResourceException;

/**
 * @property \Illuminate\Contracts\Container\Container $app
 */
trait RepositoryHelper
{
    use ValidationHelper, TransactionHelper, ExtrasHelper;    
}
