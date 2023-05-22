<?php

declare(strict_types=1);

namespace Ece2\Common\Generator;

use Psr\Container\ContainerInterface;

abstract class BaseGenerator
{
    protected string $stubDir;

    protected string $namespace;

    public const NO = 1;

    public const YES = 2;

    public function __construct(protected ContainerInterface $container)
    {
        $this->setStubDir(__DIR__ . '/Stubs/');
    }

    public function getStubDir(): string
    {
        return $this->stubDir;
    }

    public function setStubDir(string $stubDir)
    {
        $this->stubDir = $stubDir;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function replace(): self
    {
        return $this;
    }
}
