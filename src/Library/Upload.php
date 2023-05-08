<?php

declare(strict_types=1);

namespace Ece2\Common\Library;

use App\Event\UploadAfter;
use App\Service\SettingConfigService;
use Ece2\Common\Exception\NormalStatusException;
use Ece2\Common\JsonRpc\Contract\SettingConfigServiceInterface;
use Hyperf\Config\Annotation\Value;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Str;
use League\Flysystem\Filesystem;

use function Hyperf\Support\env;

class Upload
{
    /**
     * @deprecated
     */
    public const CHANNEL_LOCAL = 1;

    public const CHANNEL_OSS = 2;

    public const CHANNEL_QI_NIU = 3;

    public const CHANNEL_COS = 4;

    #[Value('file.storage')]
    protected array $config;

    protected Filesystem $filesystem;

    protected string $storageMode;

    public function __construct(FilesystemFactory $factory)
    {
        $this->storageMode = $this->getStorageMode();
        $this->filesystem = $factory->get($this->storageMode);
    }

    public function getFileSystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * 上传文件.
     * @param UploadedFile $uploadedFile
     * @param array $config
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function upload(UploadedFile $uploadedFile, array $config = []): array
    {
        $path = $this->getPath($config['path'] ?? null, $this->getMappingMode() !== self::CHANNEL_LOCAL);
        $filename = $this->getNewName() . '.' . Str::lower($uploadedFile->getExtension());

        $this->filesystem->writeStream($path . '/' . $filename, $uploadedFile->getStream()->detach());

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

        event(new UploadAfter($fileInfo));
        return $fileInfo;
    }

    /**
     * 保存网络图片.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function handleSaveNetworkImage(array $data): array
    {
        $path = $this->getPath($data['path'] ?? null, $this->getMappingMode() !== self::CHANNEL_LOCAL);
        $filename = $this->getNewName() . '.jpg';

        try {
            $content = file_get_contents($data['url']);

            $handle = fopen($data['url'], 'rb');
            $meta = stream_get_meta_data($handle);
            fclose($handle);

            $dataInfo = $meta['wrapper_data']['headers'] ?? $meta['wrapper_data'];
            $size = 0;

            foreach ($dataInfo as $va) {
                if (false !== stripos($va, 'length')) {
                    $ts = explode(':', $va);
                    $size = (int) trim(array_pop($ts));
                    break;
                }
            }

            $this->filesystem->write($path . '/' . $filename, $content);
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

        event(new UploadAfter($fileInfo));
        return $fileInfo;
    }

    /**
     * 组装url.
     * @param string $path
     */
    public function assembleUrl(?string $path, string $filename): string
    {
        $path = $this->getPath($path, true) . '/' . $filename;

        return match ($this->getMappingMode()) {
            self::CHANNEL_OSS => $this->filesystem->getUrl($path),
            default => $path
        };
    }

    /**
     * 获取存储方式.
     */
    public function getStorageMode(): string
    {
        if (is_base_system()) {
            return container()->get(SettingConfigService::class)->getConfigByKey('site_storage_mode')['value'] ?? 'local';
        }

        return container()->get(SettingConfigServiceInterface::class)->getConfigByKey('site_storage_mode')['value'] ?? 'local';
    }

    /**
     * 获取编码后的文件名.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getNewName()
    {
        return snowflake_id();
    }

    protected function getMappingMode(): int
    {
        return match ($this->storageMode) {
            'oss' => self::CHANNEL_OSS,
            'qiniu' => self::CHANNEL_QI_NIU,
            'cos' => self::CHANNEL_COS,
            default => self::CHANNEL_LOCAL, // local
        };
    }

    protected function getPath(?string $path = null, bool $isContainRoot = false): string
    {
        $uploadPath = $isContainRoot ? env('UPLOAD_PATH', '') : '';

        return $uploadPath . (empty($path) ? date('Ymd') : $path);
    }
}
