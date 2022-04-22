<?php

declare(strict_types=1);

namespace Ece2\Common\Office;

use Ece2\Common\Exception\HttpException;
use Ece2\Common\Interfaces\ModelExcelInterface;
use Ece2\Common\Response;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;

abstract class Excel
{
    public const ANNOTATION_NAME = 'Ece2\Common\Annotation\ExcelProperty';

    /**
     * @var array|null
     */
    protected ?array $annotationMate;

    /**
     * @var array
     */
    protected array $property = [];


    /**
     * @param String $dto
     */
    public function __construct(String $dto)
    {
        if (! (new $dto) instanceof ModelExcelInterface) {
            throw new HttpException(message: 'dto does not implement an interface of the ModelExcel');
        }
        $this->annotationMate = AnnotationCollector::get($dto);
        $this->parseProperty();
    }

    /**
     * @return array
     */
    public function getProperty(): array
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getAnnotationInfo(): array
    {
        return $this->annotationMate;
    }

    protected function parseProperty(): void
    {
        if (empty($this->annotationMate) || !isset($this->annotationMate['_c'])) {
            throw new HttpException(message: 'dto annotation info is empty');
        }

        foreach ($this->annotationMate['_p'] as $name => $mate) {
            $this->property[ $mate[self::ANNOTATION_NAME]->index ] = [
                'name'  => $name,
                'value' => $mate[self::ANNOTATION_NAME]->value,
                'width' => $mate[self::ANNOTATION_NAME]->width ?? null,
                'align' => $mate[self::ANNOTATION_NAME]->align ?? null,
                'headColor' => $mate[self::ANNOTATION_NAME]->headColor ?? null,
                'headBgColor' => $mate[self::ANNOTATION_NAME]->headBgColor ?? null,
                'color' => $mate[self::ANNOTATION_NAME]->color ?? null,
                'bgColor' => $mate[self::ANNOTATION_NAME]->bgColor ?? null,
            ];
        }

        ksort($this->property);
    }

    /**
     * 下载excel
     * @param string $filename
     * @param string $content
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function downloadExcel(string $filename, string $content): \Psr\Http\Message\ResponseInterface
    {
        return container()->get(Response::class)->getResponse()
            ->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('content-disposition', "attachment; filename=".rawurlencode($filename))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($content));
    }
}
