<?php

declare(strict_types=1);

namespace Ece2\Common\Generator;

use App\Model\SettingGenerateTable;
use App\Service\SettingGenerateColumnService;
use Ece2\Common\Interfaces\CodeGenerator;
use Ece2\Common\Exception\NormalStatusException;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function Hyperf\Support\make;
use function Hyperf\Support\env;

/**
 * 模型生成
 */
class ModelGenerator extends BaseGenerator implements CodeGenerator
{
    protected SettingGenerateTable $model;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息
     * @param SettingGenerateTable $model
     * @return $this
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setGenInfo(SettingGenerateTable $model): ModelGenerator
    {
        $this->model = $model;
        $this->filesystem = make(Filesystem::class);
        if (empty($model->menu_name)) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->setNamespace($this->model->namespace);
        return $this;
    }

    /**
     * 生成代码
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function generator(): void
    {
        if ($this->model->generate_type === 1) {
            $path = BASE_PATH . '/runtime/generate/php/app/Model/';
        } else {
            $path = BASE_PATH . '/app/Model/';
        }
        $this->filesystem->exists($path) || $this->filesystem->makeDirectory($path, 0755, true, true);

        $command = [
            'command' => 'gen:model',
            'table' => $this->model->table_name,
            '--with-comments' => true,
        ];

        $input = new ArrayInput($command);
        $output = new NullOutput();

        /** @var \Symfony\Component\Console\Application $application */
        $application = $this->container->get(\Hyperf\Contract\ApplicationInterface::class);
        $application->setAutoExit(false);
        if ($application->run($input, $output) === 0) {
            $modelName = Str::studly(str_replace(env('DB_PREFIX'), '', $this->model->table_name));

            // 对模型文件处理
            if ($modelName[strlen($modelName) - 1] == 's' && $modelName[strlen($modelName) - 2] != 's') {
                $oldName = Str::substr($modelName, 0, (strlen($modelName) - 1));
                $oldPath = BASE_PATH . "/app/Model/{$oldName}.php";
                $sourcePath = BASE_PATH . "/app/Model/{$modelName}.php";
                $this->filesystem->put(
                    $sourcePath,
                    str_replace($oldName, $modelName, $this->filesystem->sharedGet($oldPath))
                );
                @unlink($oldPath);
            } else {
                $sourcePath = BASE_PATH . "/app/Model/{$modelName}.php";
            }

            if (! empty($this->model->options['relations'])) {
                $this->filesystem->put(
                    $sourcePath,
                    preg_replace('/}$/', $this->getRelations() . "}", $this->filesystem->sharedGet($sourcePath))
                );
            }

            // 压缩包下载
            if ($this->model->generate_type === 1) {
                $toPath = BASE_PATH . "/runtime/generate/php/app/Model/{$modelName}.php";

                $isFile = is_file($sourcePath);

                if ($isFile) {
                    $this->filesystem->copy($sourcePath, $toPath);
                } else {
                    $this->filesystem->move($sourcePath, $toPath);
                }
            }
        } else {
            throw new NormalStatusException(t('setting.gen_model_error'), 500);
        }
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->placeholderReplace()->getCodeContent();
    }

    /**
     * 获取控制器模板地址
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . 'model.stub';
    }

    /**
     * 读取模板内容
     * @return string
     */
    protected function readTemplate(): string
    {
        return $this->filesystem->sharedGet($this->getTemplatePath());
    }

    /**
     * 占位符替换
     */
    protected function placeholderReplace(): ModelGenerator
    {
        $this->setCodeContent(str_replace(
            $this->getPlaceHolderContent(),
            $this->getReplaceContent(),
            $this->readTemplate(),
        ));

        return $this;
    }

    /**
     * 获取要替换的占位符
     */
    protected function getPlaceHolderContent(): array
    {
        return [
            '{NAMESPACE}',
            '{PROPERTY}',
            '{CLASS_NAME}',
            '{TABLE_NAME}',
            '{FILL_ABLE}',
            '{CASTS}',
            '{RELATIONS}',
        ];
    }

    /**
     * 获取要替换占位符的内容
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->initNamespace(),
            $this->initProperty(),
            $this->getClassName(),
            $this->getTableName(),
            $this->getFillAble(),
            $this->getCasts(),
            $this->getRelations(),
        ];
    }

    /**
     * 初始化模型命名空间
     * @return string
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . "\\Model";
    }

    /**
     * 初始化属性
     * @return string
     */
    protected function initProperty(): string
    {
        $data = make(SettingGenerateColumnService::class)->getList(
            ['select' => 'column_name,column_comment,column_type', 'table_id' => $this->model->id]
        );
        $propertys = [];
        foreach ($data as $column) {
            $propertys[] = sprintf(
                    ' * @property %s $%s %s',
                    $column['column_name'] === 'deleted_at' ? 'string' : $this->formatPropertyType($column['column_type']),
                    $column['column_name'],
                    $column['column_comment']
                ) . PHP_EOL;
        }

        return "/**\n" . implode($propertys) . " */";
    }

    protected function formatDatabaseType(string $type): ?string
    {
        return match ($type) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => 'integer',
            'bool', 'boolean' => 'boolean',
            default => $type,
        };
    }

    protected function formatPropertyType(string $type): ?string
    {
        $cast = $this->formatDatabaseType($type);
        return match ($cast) {
            'integer' => 'int',
            'date', 'datetime', 'timestamp' => '\Carbon\Carbon',
            'json' => 'array',
            default => 'string',
        };
    }

    /**
     * 获取类名称
     * @return string
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName();
    }

    /**
     * 获取表名称
     * @return string
     */
    protected function getTableName(): string
    {
        return $this->model->table_name;
    }

    /**
     * 获取file able
     */
    protected function getFillAble(): string
    {
        $data = make(SettingGenerateColumnService::class)->getList(
            ['select' => 'column_name', 'table_id' => $this->model->id]
        );
        $columns = [];
        foreach ($data as $column) {
            $columns[] = "'" . $column['column_name'] . "'";
        }

        return implode(', ', $columns);
    }

    /**
     * 获取casts
     */
    protected function getCasts(): string
    {
        $data = make(SettingGenerateColumnService::class)->getList(
            ['select' => 'column_name,column_type', 'table_id' => $this->model->id]
        );
        $columns = [];
        foreach ($data as $column) {
            if (substr($column['column_type'], -3) === 'int') {
                $columns[] = "'{$column['column_name']}' => 'integer'";
            } elseif ($column['column_type'] === 'timestamp') {
                $columns[] = "'{$column['column_name']}' => 'datetime'";
            }
        }

        return implode(', ', $columns);
    }

    /**
     * @return string
     */
    protected function getRelations(): string
    {
        $prefix = env('DB_PREFIX');
        if (! empty($this->model->options['relations'])) {
            $path = $this->getStubDir() . 'ModelRelation/';
            $phpCode = '';
            foreach ($this->model->options['relations'] as $relation) {
                $content = $this->filesystem->sharedGet($path . $relation['type'] . '.stub');
                $content = str_replace(
                    ['{RELATION_NAME}', '{MODEL_NAME}', '{TABLE_NAME}', '{FOREIGN_KEY}', '{LOCAL_KEY}'],
                    [$relation['name'], $relation['model'], str_replace($prefix, '', $relation['table']), $relation['foreignKey'], $relation['localKey']],
                    $content
                );
                $phpCode .= $content;
            }
            return $phpCode;
        }
        return '';
    }

    /**
     * 获取业务名称
     * @return string
     */
    public function getBusinessName(): string
    {
        return Str::studly(str_replace(env('DB_PREFIX'), '', $this->model->table_name));
    }


    /**
     * 设置代码内容
     * @param string $content
     */
    public function setCodeContent(string $content)
    {
        $this->codeContent = $content;
    }

    /**
     * 获取代码内容
     * @return string
     */
    public function getCodeContent(): string
    {
        return $this->codeContent;
    }
}
