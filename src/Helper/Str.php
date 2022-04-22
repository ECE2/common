<?php

namespace Ece2\Common\Helper;

class Str
{
    /**
     * 获取IP的区域地址
     * @param string $ip
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function ipToRegion(string $ip): string
    {
        $ip2Region = make(\Ip2Region::class);
        if (empty($ip2Region->btreeSearch($ip)['region'])) {
            return t('jwt.unknown');
        }
        $region = $ip2Region->btreeSearch($ip)['region'];
        list($country, $number, $province, $city, $network) = explode('|', $region);
        if ($country == '中国') {
            return $province . '-' . $city . ':' . $network;
        } else if ($country == '0') {
            return t('jwt.unknown');
        } else {
            return $country;
        }
    }

    /**
     * 秒数转时分格式
     * @param  $time int
     * @return string
     */
    public static function Sec2Time(int $time): string
    {
        $value = [
            'years' => 0, 'days' => 0, 'hours' => 0,
            'minutes' => 0, 'seconds' => 0,
        ];

        if ($time >= 31556926) {
            $value['years'] = floor($time / 31556926);
            $time = ($time % 31556926);
        }

        if ($time >= 86400) {
            $value['days'] = floor($time / 86400);
            $time = ($time % 86400);
        }

        if ($time >= 3600) {
            $value['hours'] = floor($time / 3600);
            $time = ($time % 3600);
        }

        if ($time >= 60) {
            $value['minutes'] = floor($time / 60);
            $time = ($time % 60);
        }

        $value['seconds'] = floor($time);

        return $value['years'] . '年' . $value['days'] . '天 ' . $value["hours"] . '小时' . $value['minutes'] . '分' . $value['seconds'] . '秒';

    }

    /**
     * 生成UUID
     * @return string
     */
    public static function getUUID(): string
    {
        $chars = md5(uniqid((string) mt_rand(), true));
        return substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
    }
}
