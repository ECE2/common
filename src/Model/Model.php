<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\Common\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;

abstract class Model extends BaseModel
{
    protected $guarded = [];

    /**
     * 允许前端传长度.
     */
    public function getPerPage(): int
    {
        /** @var RequestInterface $request */
        $request = $this->getContainer()->make(RequestInterface::class);

        return (int) $request->input('pageSize', parent::getPerPage());
    }
}
