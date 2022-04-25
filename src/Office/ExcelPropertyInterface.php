<?php

declare(strict_types=1);

namespace Ece2\Common\Office;

use Ece2\Common\Abstracts\AbstractModel;

interface ExcelPropertyInterface
{
    public function import(AbstractModel $model, ?\Closure $closure = null): bool;

    public function export(string $filename, array|\Closure $closure): \Psr\Http\Message\ResponseInterface;
}
