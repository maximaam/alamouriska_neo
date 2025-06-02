<?php

declare(strict_types=1);

namespace App\Enum;

enum PostType: int
{
    case word = 1;
    case expression = 2;
    case proverb = 3;
    case joke = 4;
}
