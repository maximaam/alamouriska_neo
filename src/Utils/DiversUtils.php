<?php

declare(strict_types=1);

namespace App\Utils;

final class DiversUtils
{
    public static function rollingClouds(): string
    {
        $clouds = '';
        $cloud = '☁️';
        $ws = '&nbsp;';
        $randomClouds = range(1, 5);

        for ($i = 0; $i <= $randomClouds; ++$i) {
            $clouds .= str_repeat($cloud, random_int(1, 2)).str_repeat($ws, random_int(1, 10));
        }

        return $clouds;
    }
}
