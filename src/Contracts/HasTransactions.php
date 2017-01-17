<?php

namespace Znck\Repositories\Contracts;

interface HasTransactions {
    public function startTransaction();

    public function commitTransaction();

    public function rollbackTransaction();
}
