<?php

declare(strict_types=1);

namespace App\Utils;

final class DiversUtils
{
    public static function rollingClouds(string $img): string
    {
        $clouds = '';
        // $cloud = '☁️';
        $cloud = '<img src="'.$img.'" height="50">';
        $ws = '&nbsp;';
        $randomClouds = random_int(1, 3);

        for ($i = 0; $i <= $randomClouds; $i++) {
            $clouds .= str_repeat($cloud, random_int(1, 2)).str_repeat($ws, random_int(10, 30));
        }

        return $clouds;
    }
}
