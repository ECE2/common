<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use App\Constants\ErrorCode;
use Ece2\Common\Annotation\Auth;
use Ece2\Common\Exception\AppException;
use Ece2\Common\Exception\TokenException;
use Ece2\Common\Interfaces\AuthenticationInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

#[Aspect]
class AuthAspect extends AbstractAspect
{
    public array $annotations = [
        Auth::class,
    ];

    /**
     * @var int aop 优先级
     */
    public ?int $priority = 100;

    public function __construct(protected AuthenticationInterface $authentication)
    {
    }

    /**
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotationMetadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 优先 method, 其次 class
        $auth = null;
        if (isset($annotationMetadata->method[Auth::class])) {
            $auth = $annotationMetadata->method[Auth::class];
        } elseif (isset($annotationMetadata->class[Auth::class])) {
            $auth = $annotationMetadata->class[Auth::class];
        }

        try {
            $isCheck = $this->authentication->check('', $auth?->scene ?? 'api');
        } catch (\Exception $e) {
            console()->info($e->getMessage());
            console()->info($e->getTraceAsString());
            throw new AppException(ErrorCode::ERROR_401_MEMBER_NOT_LOGIN);
        }

        if ($isCheck) {
            return $proceedingJoinPoint->process();
        }

        throw new TokenException(t('jwt.validate_fail'));
    }
}
