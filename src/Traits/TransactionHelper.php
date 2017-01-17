<?php

namespace Znck\Repositories\Traits;


trait TransactionHelper {
    /**
     * A flag to denote whether running in a transaction or not.
     *
     * @var boolean
     */
    protected $inTransaction = false;

    public function startTransaction() {
        $this->app->make('db')->beginTransaction();
        $this->inTransaction = true;
    }

    public function commitTransaction() {
        if (!$this->inTransaction) {
            $this->app->make('db')->commit();
            $this->inTransaction = false;
        }
    }

    public function rollbackTransaction() {
        if ($this->inTransaction) {
            $this->app->make('db')->rollback();
            $this->inTransaction = false;
        }
    }
}
