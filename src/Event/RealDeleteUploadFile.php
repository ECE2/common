<?php

declare(strict_types=1);

namespace Ece2\Common\Event;

use League\Flysystem\Filesystem;

class RealDeleteUploadFile
{
    protected bool $confirm = true;

    public function __construct(protected $model, protected Filesystem $filesystem)
    {
    }

    /**
     * 获取当前模型实例
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 获取文件处理系统
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * 是否删除
     * @return bool
     */
    public function getConfirm(): bool
    {
        return $this->confirm;
    }

    public function setConfirm(bool $confirm): void
    {
        $this->confirm = $confirm;
    }
}
