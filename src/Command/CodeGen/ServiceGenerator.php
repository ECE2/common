<?php
declare(strict_types=1);

namespace Ece2\Common\Command\CodeGen;

use Hyperf\Utils\CodeGen\Project;

class ServiceGenerator extends BaseGenerator
{
    public function generate(string $modelClass): string
    {
        $project = new Project();
        $class = $project->namespace($this->input->getOption('path') . '/Service') . class_basename($modelClass) . 'Service';
        $path = BASE_PATH . '/' . $project->path($class);

        if (!file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($class, $modelClass, class_basename($modelClass)));
            $this->output->writeln(sprintf('<info>Service %s was created.</info>', $class));
        }

        return $class;
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $className, $uses, $modelClass): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Service.stub');

        $this->replaceNamespace($stub, $className)
            ->replaceClass($stub, $className)
            ->replaceUses($stub, $uses)
            ->replace($stub, '%MODEL_CLASS%', $modelClass);

        return $stub;
    }
}
