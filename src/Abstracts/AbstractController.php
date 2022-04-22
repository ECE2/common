<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Library\TraceId;
use Ece2\Common\Traits\ControllerTrait;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Context;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractController
{
    use ControllerTrait;
}
