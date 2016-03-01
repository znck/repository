<?php namespace Znck\Repositories;

use Illuminate\Support\ServiceProvider;
use Znck\Repositories\Console\MakeRepositoryCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([MakeRepositoryCommand::class]);
    }
}
