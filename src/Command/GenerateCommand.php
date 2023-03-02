<?php

declare(strict_types=1);

namespace Ece2\Common\Command;

use Ece2\Common\Command\CodeGen\ControllerGenerator;
use Ece2\Common\Command\CodeGen\ModelGenerator;
use Ece2\Common\Command\CodeGen\ServiceGenerator;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GenerateCommand extends HyperfCommand
{
    protected ?string $name = 'gen:code';

    public function handle()
    {
        $models = make(ModelGenerator::class, [$this->input, $this->output])->generate();

        foreach ($models as $modelClass => $model) {
            $serviceClass = make(ServiceGenerator::class, [$this->input, $this->output])->generate($modelClass);
            make(ControllerGenerator::class, [$this->input, $this->output])->generate($modelClass, $serviceClass);
        }

        $this->output->writeln('<info>执行完成, 建议执行以下命令进行代码格式化</info>');
        $this->output->writeln('composer run-script cs-fix');
    }

    public function configure()
    {
        parent::configure();

        $this->setDescription('代码生成工具');

        $this->addArgument('table', InputArgument::OPTIONAL, '表');

        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, '数据连接池', 'default');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, '基础路径', 'app');
        $this->addOption('force-casts', 'F', InputOption::VALUE_NONE, 'Whether force generate the casts for model.');
        $this->addOption('prefix', 'P', InputOption::VALUE_OPTIONAL, 'What prefix that you want the Model set.');
        $this->addOption('inheritance', 'i', InputOption::VALUE_OPTIONAL, 'The inheritance that you want the Model extends.');
        $this->addOption('uses', 'U', InputOption::VALUE_OPTIONAL, 'The default class uses of the Model.');
        $this->addOption('refresh-fillable', 'R', InputOption::VALUE_NONE, 'Whether generate fillable argement for model.');
        $this->addOption('table-mapping', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Table mappings for model.');
        $this->addOption('ignore-tables', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore tables for creating models.');
        $this->addOption('with-comments', null, InputOption::VALUE_NONE, 'Whether generate the property comments for model.');
        $this->addOption('with-ide', null, InputOption::VALUE_NONE, 'Whether generate the ide file for model.');
        $this->addOption('visitors', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Custom visitors for ast traverser.');
        $this->addOption('property-case', null, InputOption::VALUE_OPTIONAL, 'Which property case you want use, 0: snake case, 1: camel case.');
    }
}
