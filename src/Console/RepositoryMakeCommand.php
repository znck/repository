<?php namespace Znck\Repositories\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Znck\Repositories\Repository;

/**
 * @property \Illuminate\Foundation\Application $laravel
 */
class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    protected function getStub()
    {
        return dirname(dirname(__DIR__)).'/resources/stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\'.$this->getRepositoriesDirectory();
    }

    protected function parseModel($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->parseModel(trim($rootNamespace, '\\').'\\'.$name);
    }

    protected function parseName($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (! Str::endsWith($name, 'Repository')) {
            $name .= 'Repository';
        }

        if (Str::contains($name, ['Models', 'Eloquent'])) {
            $name = str_replace(['Models', 'Eloquent'], '', $name);
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        $name = str_replace('\\\\', '\\', $name);

        if (Str::startsWith($name, $rootNamespace)) {
            if (! Str::contains($name, 'Repositories')) {
                return str_replace($rootNamespace, $this->getDefaultNamespace($rootNamespace), $name);
            }

            return $name;
        }

        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $repository = config('repository.base_repository', Repository::class);

        $this->replaceRepository($stub, $repository);
        $this->replaceModel($stub);

        return $stub;
    }

    protected function replaceRepository(&$stub, $class)
    {
        if (! hash_equals(Repository::class, $class)) {
            $class .= ' as Repository';
        }

        $stub = str_replace('DummyBaseRepository', $class, $stub);

        return $this;
    }

    protected function replaceModel(&$stub)
    {
        $name = $this->parseModel($this->getNameInput());

        if (! class_exists($name)) {
            $comments = '// FIXME: Add model class name. Detected: '.$name;
        }

        $namespace = "use ${name};";
        $name = class_basename($name);
        $stub = str_replace('DummyModelNamespace', $namespace, $stub);
        $stub = str_replace('DummyModelClass', $name, $stub);
        $stub = str_replace('HelperComments', $comments ?? '', $stub);

        return $this;
    }

    protected function getRepositoriesDirectory()
    {
        return 'Repositories';
    }
}
