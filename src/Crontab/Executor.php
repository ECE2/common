<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab;

use App\Service\SettingCrontabLogService;
use Carbon\Carbon;
use Closure;
use Ece2\Common\JsonRpc\Contract\SettingCrontabServiceInterface;
use Ece2\Common\Library\Log;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Logger\Logger;
use Ece2\Common\Crontab\Mutex\RedisServerMutex;
use Ece2\Common\Crontab\Mutex\RedisTaskMutex;
use Ece2\Common\Crontab\Mutex\ServerMutex;
use Ece2\Common\Crontab\Mutex\TaskMutex;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Swoole\Timer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class Executor
{
    /**
     * @var TaskMutex
     */
    protected TaskMutex $taskMutex;

    /**
     * @var ServerMutex
     */
    protected ServerMutex $serverMutex;

    /**
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * 执行定时任务
     * @param Crontab $crontab
     * @param bool $run
     * @return bool|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function execute(Crontab $crontab, bool $run = false): ?bool
    {
        if ((!$crontab instanceof Crontab || !$crontab->getExecuteTime()) && !$run) {
            return null;
        }
        $diff = 0;
        !$run && $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        $callback = null;
        switch ((int) $crontab->getType()) {
            case 2: // SettingCrontab::CLASS_CRONTAB
                $class = $crontab->getCallback();
                $method = 'execute';
                $parameters = $crontab->getParameter() ?: null;
                if ($class && class_exists($class) && method_exists($class, $method)) {
                    $callback = function () use ($class, $method, $parameters, $crontab) {
                        $runnable = function () use ($class, $method, $parameters, $crontab) {
                            try {
                                $result = true;
                                $res = null;
                                $instance = make($class);
                                if ($parameters && is_array($parameters)) {
                                    $res = $instance->{$method}(...$parameters);
                                } else {
                                    $res = $instance->{$method}();
                                }
                            } catch (\Throwable $throwable) {
                                $result = false;
                            } finally {
                                $this->logResult($crontab, $result, isset($throwable) ? $throwable->getMessage() : $res);
                            }
                        };

                        Coroutine::create($this->decorateRunnable($crontab, $runnable));
                    };
                }
                break;
            case 1: // SettingCrontab::COMMAND_CRONTAB
                $command = ['command' => $crontab->getCallback()];
                $parameter = $crontab->getParameter() ?: '{}';
                $input = make(ArrayInput::class, array_merge($command, json_decode($parameter, true)));
                $output = make(NullOutput::class);
                $application = $this->container->get(ApplicationInterface::class);
                $application->setAutoExit(false);
                $callback = function () use ($application, $input, $output, $crontab) {
                    $runnable = function () use ($application, $input, $output, $crontab) {
                        $result = $application->run($input, $output);
                        $this->logResult($crontab, $result === 0, $result);
                    };
                    $this->decorateRunnable($crontab, $runnable)();
                };
                break;
            case 3: // SettingCrontab::URL_CRONTAB
                $clientFactory = $this->container->get(ClientFactory::class);
                $client = $clientFactory->create();
                $callback = function () use ($client, $crontab) {
                    $runnable = function () use ($client, $crontab) {
                        try {
                            $response = $client->get($crontab->getCallback());
                            $result = $response->getStatusCode() === 200;
                        } catch (\Throwable $throwable) {
                            $result = false;
                        }
                        $this->logResult(
                            $crontab,
                            $result,
                            (!$result && isset($response)) ? $response->getBody() : ''
                        );
                    };
                    $this->decorateRunnable($crontab, $runnable)();
                };
                break;
            case 4: // SettingCrontab::EVAL_CRONTAB
                $callback = function () use ($crontab) {
                    $runnable = function () use ($crontab) {
                        $result = true;
                        try {
                            eval($crontab->getCallback());
                        } catch (\Throwable $throwable) {
                            $result = false;
                        }
                        $this->logResult($crontab, $result, isset($throwable) ? $throwable->getMessage() : '');
                    };
                    $this->decorateRunnable($crontab, $runnable)();
                };
                break;
        }
        $callback && Timer::after($diff > 0 ? $diff * 1000 : 1, $callback);

        return true;
    }

    protected function runInSingleton(Crontab $crontab, Closure $runnable): Closure
    {
        return function () use ($crontab, $runnable) {
            $taskMutex = $this->getTaskMutex();

            if ($taskMutex->exists($crontab) || !$taskMutex->create($crontab)) {
                Log::info(sprintf('Crontab task [%s] skipped execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                return;
            }

            try {
                $runnable();
            } finally {
                $taskMutex->remove($crontab);
            }
        };
    }

    protected function getTaskMutex(): TaskMutex
    {
        if (!$this->taskMutex) {
            $this->taskMutex = $this->container->has(TaskMutex::class)
                ? $this->container->get(TaskMutex::class)
                : $this->container->get(RedisTaskMutex::class);
        }
        return $this->taskMutex;
    }

    protected function runOnOneServer(Crontab $crontab, Closure $runnable): Closure
    {
        return function () use ($crontab, $runnable) {
            $taskMutex = $this->getServerMutex();

            if (!$taskMutex->attempt($crontab)) {
                Log::info(sprintf('Crontab task [%s] skipped execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                return;
            }

            $runnable();
        };
    }

    protected function getServerMutex(): ServerMutex
    {
        if (!$this->serverMutex) {
            $this->serverMutex = $this->container->has(ServerMutex::class)
                ? $this->container->get(ServerMutex::class)
                : $this->container->get(RedisServerMutex::class);
        }
        return $this->serverMutex;
    }

    /**
     * @param Crontab $crontab
     * @param Closure $runnable
     * @return Closure
     */
    protected function decorateRunnable(Crontab $crontab, Closure $runnable): Closure
    {
        if ($crontab->isSingleton()) {
            $runnable = $this->runInSingleton($crontab, $runnable);
        }

        if ($crontab->isOnOneServer()) {
            $runnable = $this->runOnOneServer($crontab, $runnable);
        }

        return $runnable;
    }

    protected function logResult(Crontab $crontab, bool $isSuccess, $result = '')
    {
        if ($isSuccess) {
            Log::info(sprintf('Crontab task [%s] executed successfully at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
        } else {
            Log::error(sprintf('Crontab task [%s] failed execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
        }

        $data = [
            'crontab_id' => $crontab->getCrontabId(),
            'name' => $crontab->getName(),
            'target' => $crontab->getCallback(),
            'parameter' => $crontab->getParameter(),
            'exception_info' => $result,
            'status' => $isSuccess ? '0' : '1',
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (is_base_system()) {
            $this->container->get(SettingCrontabLogService::class)->save($data);
        } else {
            $this->container->get(SettingCrontabServiceInterface::class)->save($data);
        }
    }
}
