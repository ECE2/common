<?php

declare(strict_types=1);

namespace Ece2\Common\Office;

use Ece2\Common\Model\Model;

interface ExcelPropertyInterface
{
    public function import(Model $model, ?\Closure $closure = null): bool;

    public function export(string $filename, array|\Closure $closure): \Psr\Http\Message\ResponseInterface;
}
