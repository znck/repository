<?php namespace Znck\Repositories;

use Illuminate\Support\ServiceProvider;
use Znck\Repositories\Console\RepositoryMakeCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        require_once __DIR__.'/helper.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.make.repository', RepositoryMakeCommand::class);
        $this->commands('command.make.repository');
    }

    public function provides()
    {
        return ['command.make.repository'];
    }
}
