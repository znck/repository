<?php namespace Znck\Repositories\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Znck\Repositories\Repository;

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
    
    protected $model;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return dirname(dirname(__DIR__)).'/resources/stubs/repository.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\'.str_replace('/', '\\', $this->getRepositoriesDirectory());
    }

    protected function parseName($name) {
        if (Str::endsWith($name, 'Repository')) {
            $this->model = Str::replaceLast('Repository', '', $name);
        } else {
            $this->model = $name;
            $name .= 'Repository';
        }
        return parent::parseName($name);
    }
    
    protected function buildClass($name) {
        $stub = parent::buildClass($name);

        $repository = config('repository.base_repository', Repository::class);
        $this->replaceRepository($stub, $repository);

        $this->replaceModel($stub, $this->model);

        return $stub;
    }

    protected function replaceRepository(&$stub, $class) {
        $stub = str_replace('DummyBaseRepository', $class, $stub);

        return $this;
    }

    protected function replaceModel(&$stub, $name) {
        $name = $this->parseModelName($name);
        
        if (!class_exists($name)) {
            $comments = '// FIXME: Add model class name. Detected: ' . $name;
        }

        $namespace = "use ${name};";
        $name = class_basename($name);
        $stub = str_replace('DummyModelNamespace', $namespace, $stub);
        $stub = str_replace('DummyModelClass', $name, $stub);
        $stub = str_replace('HelperComments', $comments ?? '', $stub);

        return $this;
    }

    protected function parseModelName($name) {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        if (Str::startsWith($name, $this->getDefaultNamespace($rootNamespace))) {
            $name = str_replace($this->getDefaultNamespace($rootNamespace), '', $name);
        } elseif (Str::contains($name, $this->getRepositoriesDirectory())) {
            $name = substr($name, strpos($name, $this->getRepositoriesDirectory()) + strlen($this->getRepositoriesDirectory()));
        }

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return str_replace('\\\\', '\\', $rootNamespace.'\\'.trim($name, '\\'));
    }

    /**
     * @return string
     */
    protected function getRepositoriesDirectory() {
        return 'Repositories';
    }
}
