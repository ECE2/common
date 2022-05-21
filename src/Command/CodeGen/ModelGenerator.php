<?php

declare(strict_types=1);

namespace Ece2\Common\Command\CodeGen;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\Ast\GenerateModelIDEVisitor;
use Hyperf\Database\Commands\Ast\ModelRewriteConnectionVisitor;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Builder;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelGenerator extends BaseGenerator
{
    protected ConnectionResolverInterface $resolver;

    protected ConfigInterface $config;

    protected Parser $astParser;

    protected PrettyPrinterAbstract $printer;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->resolver = container()->get(ConnectionResolverInterface::class);
        $this->config = container()->get(ConfigInterface::class);
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    public function generate(): array
    {
        $table = $this->input->getArgument('table');
        $pool = $this->input->getOption('pool');

        $option = new ModelOption();
        $option->setPool($pool)
            ->setPath($this->getOption('path', 'commands.gen:model.path', $pool, 'app'))
            ->setPrefix($this->getOption('prefix', 'prefix', $pool, ''))
            ->setInheritance($this->getOption('inheritance', 'commands.gen:model.inheritance', $pool, 'Model'))
            ->setUses($this->getOption('uses', 'commands.gen:model.uses', $pool, ''))
            ->setForceCasts($this->getOption('force-casts', 'commands.gen:model.force_casts', $pool, false))
            ->setRefreshFillable($this->getOption('refresh-fillable', 'commands.gen:model.refresh_fillable', $pool, false))
            ->setTableMapping($this->getOption('table-mapping', 'commands.gen:model.table_mapping', $pool, []))
            ->setIgnoreTables($this->getOption('ignore-tables', 'commands.gen:model.ignore_tables', $pool, []))
            ->setWithComments($this->getOption('with-comments', 'commands.gen:model.with_comments', $pool, false))
            ->setWithIde($this->getOption('with-ide', 'commands.gen:model.with_ide', $pool, false))
            ->setVisitors($this->getOption('visitors', 'commands.gen:model.visitors', $pool, []))
            ->setPropertyCase($this->getOption('property-case', 'commands.gen:model.property_case', $pool));

        if ($table) {
            [$tableClass, $value] = $this->createModel($table, $option);
            return [$tableClass => $value];
        }

        // TODO
//        return $this->createModels($option);
    }

    protected function getColumns($className, $columns, $forceCasts): array
    {
        /** @var Model $model */
        $model = new $className();
        $dates = $model->getDates();
        $casts = [];
        if (! $forceCasts) {
            $casts = $model->getCasts();
        }

        foreach ($dates as $date) {
            if (! isset($casts[$date])) {
                $casts[$date] = 'datetime';
            }
        }

        foreach ($columns as $key => $value) {
            $columns[$key]['cast'] = $casts[$value['column_name']] ?? null;
        }

        return $columns;
    }

    protected function getOption(string $name, string $key, string $pool = 'default', $default = null)
    {
        $result = $this->input->getOption($name);
        $nonInput = null;
        if (in_array($name, ['force-casts', 'refresh-fillable', 'with-comments', 'with-ide'])) {
            $nonInput = false;
        }
        if (in_array($name, ['table-mapping', 'ignore-tables', 'visitors'])) {
            $nonInput = [];
        }

        if ($result === $nonInput) {
            $result = $this->config->get("databases.{$pool}.{$key}", $default);
        }

        return $result;
    }

    protected function createModel(string $table, ModelOption $option)
    {
        $builder = $this->getSchemaBuilder($option->getPool());
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
        $columns = $this->formatColumns($builder->getColumnTypeListing($table));

        $project = new Project();
        $class = $option->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        $class = $project->namespace($option->getPath() . '/Model') . $class;
        $path = BASE_PATH . '/' . $project->path($class);

        if (! file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($table, $class, $option, $columns));
        }

        $columns = $this->getColumns($class, $columns, $option->isForceCasts());

        $stms = $this->astParser->parse(file_get_contents($path));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitor::class, [
            'class' => $class,
            'columns' => $columns,
            'option' => $option,
        ]));
        $traverser->addVisitor(make(ModelRewriteConnectionVisitor::class, [$class, $option->getPool()]));
        $data = make(ModelData::class)->setClass($class)->setColumns($columns);
        foreach ($option->getVisitors() as $visitorClass) {
            $traverser->addVisitor(make($visitorClass, [$option, $data]));
        }
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);

        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));

        if ($option->isWithIde()) {
            $this->generateIDE($code, $option, $data);
        }

        return [
            $class,
            [
                'columns' => $columns,
            ],
        ];
    }

    protected function buildClass(string $table, string $name, ModelOption $option, array $columns): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        // 根据字段 自动加上 use Operator 和 use SoftDeletes
        $usesInClass = [];
        $usesCustom = [];
        $columnNameList = array_column($columns, 'column_name');
        if (in_array('created_by', $columnNameList, true)) {
            $usesInClass[] = 'use Operator;';
            $usesCustom[] = 'use Ece2\Common\Model\Traits\Operator;';
        }
        if (in_array('deleted_at', $columnNameList, true)) {
            $usesInClass[] = 'use SoftDeletes;';
            $usesCustom[] = 'use Hyperf\Database\Model\SoftDeletes;';
        }
        // 判断是否使用 雪花 trait (找到主键 && 主键不自增)
        if (($primary = collect($columns)
            ->filter(fn ($column) => $column['column_key'] === 'PRI')
            ->first())
            && ! Str::contains($primary['extra'], 'auto_increment')) {
            $usesInClass[] = 'use Snowflake;';
            $usesCustom[] = 'use Hyperf\Snowflake\Concern\Snowflake;';
        }
        $memberVariables = [];
        // 判断是否使用 $timestamps
        if (collect($columns)
            ->filter(fn ($column) => in_array($column['column_key'], ['created_at', 'updated_at']))
            ->isEmpty()) {
            $memberVariables[] = 'public $timestamps = false;';
        }

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceConnection($stub, $option->getPool())
            ->replaceUses($stub, $option->getUses())
            ->replace($stub, '%USES_CUSTOM%', ! empty($usesCustom) ? implode("\n", $usesCustom) : '')
            ->replace($stub, '%USES_IN_CLASS%', ! empty($usesInClass) ? implode("\n", $usesInClass) : '')
            ->replace($stub, '%MEMBER_VARIABLES%', ! empty($memberVariables) ? implode("\n", $memberVariables) : '')
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    protected function replaceInheritance(string &$stub, string $inheritance): self
    {
        $stub = str_replace(
            ['%INHERITANCE%'],
            [$inheritance],
            $stub
        );

        return $this;
    }

    protected function replaceConnection(string &$stub, string $connection): self
    {
        $stub = str_replace(
            ['%CONNECTION%'],
            [$connection],
            $stub
        );

        return $this;
    }

    protected function replaceTable(string $stub, string $table): string
    {
        return str_replace('%TABLE%', $table, $stub);
    }

    protected function formatColumns(array $columns): array
    {
        return array_map(function ($item) {
            return array_change_key_case($item, CASE_LOWER);
        }, $columns);
    }

    protected function getSchemaBuilder(string $poolName): Builder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function generateIDE(string $code, ModelOption $option, ModelData $data)
    {
        $stms = $this->astParser->parse($code);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(GenerateModelIDEVisitor::class, [$option, $data]));
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);
        $class = str_replace('\\', '_', $data->getClass());
        $path = BASE_PATH . '/runtime/ide/' . $class . '.php';
        $this->mkdir($path);
        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model IDE %s was created.</info>', $data->getClass()));
    }

    protected function getPath(string $name): string
    {
        return BASE_PATH . '/' . str_replace('\\', '/', $name) . '.php';
    }
}
