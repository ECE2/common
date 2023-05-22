<?php

declare(strict_types=1);

namespace Ece2\Common\Generator;

use App\Model\SettingGenerateTable;
use Ece2\Common\Interfaces\CodeGenerator;
use Hyperf\Support\Filesystem\Filesystem;
use Ece2\Common\Exception\NormalStatusException;
use Hyperf\Stringable\Str;

use function Hyperf\Support\make;
use function Hyperf\Support\env;

/**
 * 服务类生成
 */
class ServiceGenerator extends BaseGenerator implements CodeGenerator
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
    public function setGenInfo(SettingGenerateTable $model): ServiceGenerator
    {
        $this->model = $model;
        $this->filesystem = make(Filesystem::class);
        if (empty($model->menu_name)) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->setNamespace($this->model->namespace);
        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        if ($this->model->generate_type === 1) {
            $path = BASE_PATH . '/runtime/generate/php/app/Service/';
        } else {
            $path = BASE_PATH . '/app/Service/';
        }
        $this->filesystem->exists($path) || $this->filesystem->makeDirectory($path, 0755, true, true);
        $this->filesystem->put($path . "{$this->getClassName()}.php", $this->replace()->getCodeContent());
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->replace()->getCodeContent();
    }

    /**
     * 获取生成的类型
     * @return string
     */
    public function getType(): string
    {
        return ucfirst($this->model->type);
    }

    /**
     * 获取模板地址
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . $this->getType() . '/service.stub';
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
    protected function placeholderReplace(): ServiceGenerator
    {
        $this->setCodeContent(str_replace(
            $this->getPlaceHolderContent(),
            $this->getReplaceContent(),
            $this->readTemplate()
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
            '{USE}',
            '{COMMENT}',
            '{CLASS_NAME}',
            '{MODEL}',
            '{FIELD_ID}',
            '{FIELD_PID}'
        ];
    }

    /**
     * 获取要替换占位符的内容
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->initNamespace(),
            $this->getUse(),
            $this->getComment(),
            $this->getClassName(),
            $this->getModelName(),
            $this->getFieldIdName(),
            $this->getFieldPidName(),
        ];
    }

    /**
     * 初始化服务类命名空间
     * @return string
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . "\\Service";
    }

    /**
     * 获取控制器注释
     * @return string
     */
    protected function getComment(): string
    {
        return $this->model->menu_name . '服务类';
    }

    /**
     * 获取使用的类命名空间
     * @return string
     */
    protected function getUse(): string
    {
        return <<<UseNamespace
use {$this->getNamespace()}\\Model\\{$this->getBusinessName()};
UseNamespace;
    }

    /**
     * 获取控制器类名称
     * @return string
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName() . 'Service';
    }

    /**
     * 获取 Model 类名称
     * @return string
     */
    protected function getModelName(): string
    {
        return $this->getBusinessName();
    }

    /**
     * 获取树表ID
     * @return string
     */
    protected function getFieldIdName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->model->options['tree_id'] ?? false) {
                return $this->model->options['tree_id'];
            } else {
                return 'id';
            }
        }
        return '';
    }

    /**
     * 获取树表父ID
     * @return string
     */
    protected function getFieldPidName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->model->options['tree_pid'] ?? false) {
                return $this->model->options['tree_pid'];
            } else {
                return 'parent_id';
            }
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
