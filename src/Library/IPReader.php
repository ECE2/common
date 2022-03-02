<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\HyperfCommon\Library;

use Hyperf\ServiceGovernance\Exception\IPReadFailedException;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\Network;

class IPReader implements IPReaderInterface
{
    public function read(): string
    {
        try {
            // 使用配置了的服务发现地址
            // 服务注册时使用的地址, 由于在容器内, 框架获取的 IP 地址不对, 需要手动修改
            if (($serviceGovernanceHost = env('APP_SERVICE_GOVERNANCE_HOST'))
                && ! in_array($serviceGovernanceHost, ['0.0.0.0', 'localhost'])) {
                return $serviceGovernanceHost;
            }

            return Network::ip();
        } catch (\Throwable $throwable) {
            throw new IPReadFailedException($throwable->getMessage());
        }
    }
}
