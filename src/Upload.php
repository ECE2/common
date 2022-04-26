<?php

declare(strict_types=1);

namespace Ece2\Common;

use App\Service\SettingConfigService;
use Ece2\Common\Event\UploadAfter;
use Ece2\Common\Exception\NormalStatusException;
use Ece2\Common\JsonRpc\Contract\SettingConfigServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Str;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

// TODO microservice

class Upload
{
    #[Inject]
    protected FilesystemFactory $factory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    #[Inject]
    protected EventDispatcherInterface $evDispatcher;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * 存储配置信息
     * @var array
     */
    protected array $config;

    /**
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = config('file.storage');
        $this->filesystem = $this->factory->get($this->getStorageMode());
    }

    /**
     * 获取文件操作处理系统
     * @return Filesystem
     */
    public function getFileSystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * 上传文件
     * @param UploadedFile $uploadedFile
     * @param array $config
     * @return array
     * @throws \League\Flysystem\FileExistsException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function upload(UploadedFile $uploadedFile, array $config = []): array
    {
        return $this->handleUpload($uploadedFile, $config);
    }

    /**
     * 处理上传
     * @param UploadedFile $uploadedFile
     * @param array $config
     * @return array
     * @throws \League\Flysystem\FileExistsException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function handleUpload(UploadedFile $uploadedFile, array $config): array
    {
        $path = $this->getPath($config['path'] ?? null, $this->getMappingMode() !== 1);
        $filename = $this->getNewName() . '.' . Str::lower($uploadedFile->getExtension());
        $this->filesystem->writeStream($path . '/' . $filename, $uploadedFile->getStream()->detach());
        // TODO
//        if (!$this->filesystem->writeStream($path . '/' . $filename, $uploadedFile->getStream()->detach())) {
//            throw new NormalStatusException((string) $uploadedFile->getError(), 500);
//        }

        $fileInfo = [
            'storage_mode' => $this->getMappingMode(),
            'origin_name' => $uploadedFile->getClientFilename(),
            'object_name' => $filename,
            'mime_type' => $uploadedFile->getClientMediaType(),
            'storage_path' => $path,
            'suffix' => Str::lower($uploadedFile->getExtension()),
            'size_byte' => $uploadedFile->getSize(),
            'size_info' => format_size($uploadedFile->getSize() * 1024),
            'url' => $this->assembleUrl($config['path'] ?? null, $filename),
        ];

        $this->evDispatcher->dispatch(new UploadAfter($fileInfo));

        return $fileInfo;
    }

    /**
     * 保存网络图片
     * @param array $data
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function handleSaveNetworkImage(array $data): array
    {
        $path = $this->getPath($data['path'] ?? null, $this->getMappingMode() !== 1);
        $filename = $this->getNewName() . '.jpg';

        try {
            $content = file_get_contents($data['url']);

            $handle = fopen($data['url'], 'rb');
            $meta = stream_get_meta_data($handle);
            fclose($handle);

            $dataInfo = $meta['wrapper_data']['headers'] ?? $meta['wrapper_data'];
            $size = 0;

            foreach ($dataInfo as $va) {
                if (preg_match('/length/iU', $va)) {
                    $ts = explode(':', $va);
                    $size = intval(trim(array_pop($ts)));
                    break;
                }
            }

            if (!$this->filesystem->write($path . '/' . $filename, $content)) {
                throw new \Exception(t('network_image_save_fail'));
            }

        } catch (\Throwable $e) {
            throw new NormalStatusException($e->getMessage(), 500);
        }

        $fileInfo = [
            'storage_mode' => $this->getMappingMode(),
            'origin_name' => md5((string) time()) . '.jpg',
            'object_name' => $filename,
            'mime_type' => 'image/jpg',
            'storage_path' => $path,
            'suffix' => 'jpg',
            'size_byte' => $size,
            'size_info' => format_size($size * 1024),
            'url' => $this->assembleUrl($data['path'] ?? null, $filename),
        ];

        $this->evDispatcher->dispatch(new UploadAfter($fileInfo));

        return $fileInfo;
    }

    /**
     * @param string $config
     * @param false $isContainRoot
     * @return string
     */
    protected function getPath(?string $path = null, bool $isContainRoot = false): string
    {
        $uploadfile = $isContainRoot ? '/' . env('UPLOAD_PATH', 'uploadfile') . '/' : '';
        return empty($path) ? $uploadfile . date('Ymd') : $uploadfile . $path;
    }

    /**
     * 创建目录
     * @param string $name
     * @return bool
     */
    public function createUploadDir(string $name): bool
    {
        return $this->filesystem->createDir($name);
    }

    /**
     * 获取目录内容
     * @param string $path
     * @return array
     */
    public function listContents(string $path = ''): array
    {
        return $this->filesystem->listContents($path);
    }

    /**
     * 获取目录
     * @param string $path
     * @param bool $isChildren
     * @return array
     */
    public function getDirectory(string $path, bool $isChildren): array
    {
        $contents = $this->filesystem->listContents($path, $isChildren);
        $dirs = [];
        foreach ($contents as $content) {
            if ($content['type'] == 'dir') {
                $dirs[] = $content;
            }
        }
        return $dirs;
    }

    /**
     * 组装url
     * @param string $path
     * @param string $filename
     * @return string
     */
    public function assembleUrl(?string $path, string $filename): string
    {
        return $this->getPath($path, true) . '/' . $filename;
    }

    /**
     * 获取存储方式
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getStorageMode(): string
    {
        if (is_base_system()) {
            return $this->container->get(SettingConfigService::class)->getConfigByKey('site_storage_mode')['value'] ?? 'local';
        }

        return $this->container->get(SettingConfigServiceInterface::class)->getConfigByKey('site_storage_mode')['value'] ?? 'local';
    }

    /**
     * 获取编码后的文件名
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getNewName(): string
    {
        return snowflake_id();
    }

    /**
     * @return int
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getMappingMode(): int
    {
        return match ($this->getStorageMode()) {
            'local' => 1,
            'oss' => 2,
            'qiniu' => 3,
            'cos' => 4,
            default => 1,
        };
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getProtocol(): string
    {
        return $this->container->get(Request::class)->getScheme();
    }
}
