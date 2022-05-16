<?php

declare(strict_types=1);

namespace Ece2\Common\Command\CodeGen;

use App\Model\Model;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;

class ControllerGenerator extends BaseGenerator
{
    public function generate(string $modelClass, string $serviceClass): string
    {
        $project = new Project();
        $class = $project->namespace($this->input->getOption('path') . '/Controller') . class_basename($modelClass) . 'Controller';
        $path = BASE_PATH . '/' . $project->path($class);
        $routePrefix = sprintf('api/%s/%s', config('app_name'), Str::lower(Str::snake(class_basename($modelClass), '-')));

        if (! file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($class, $serviceClass, class_basename($serviceClass), $routePrefix));
            $this->output->writeln(sprintf('<info>Controller %s was created.</info>', $class));
        }

        return $class;
    }

    /**
     * Build the class with the given name.
     * @param mixed $uses
     * @param mixed $serviceClass
     * @param mixed $routePrefix
     */
    protected function buildClass(string $className, $uses, $serviceClass, $routePrefix): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Controller.stub');

        $this->replaceNamespace($stub, $className)
            ->replaceClass($stub, $className)
            ->replaceUses($stub, $uses)
            ->replace($stub, '%SERVICE_CLASS%', $serviceClass)
            ->replace($stub, '%ROUTE_PREFIX%', $routePrefix);

        return $stub;
    }
}
