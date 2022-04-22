<?php

declare(strict_types=1);

namespace Ece2\Common\Helper;

class Captcha
{
    /**
     * @return array
     */
    public function getCaptchaInfo(): array
    {
        $conf = new \EasySwoole\VerifyCode\Conf();
        $conf->setUseCurve()->setUseNoise();
        $validCode = new \EasySwoole\VerifyCode\VerifyCode($conf);
        $draw = $validCode->DrawCode();

        return [
            'code' => \Hyperf\Utils\Str::lower($draw->getImageCode()),
            'image' => $draw->getImageByte()
        ];
    }
}
