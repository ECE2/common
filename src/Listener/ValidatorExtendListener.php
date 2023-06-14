<?php

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

#[Listener]
class ValidatorExtendListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /**  @var ValidatorFactoryInterface $factory */
        $factory = $event->validatorFactory;
        // 注册身份证验证器
        $factory->extend('id_card', function ($attribute, $value, $parameters, $validator) {
            return id_card_checksum18($value);
        });

        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展 :id_card 占位符
        $factory->replacer('id_card', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':id_card', $attribute, $message);
        });
    }

}
