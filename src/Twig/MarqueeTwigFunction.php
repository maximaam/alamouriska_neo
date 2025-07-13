<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\DiversUtils;
use Twig\Attribute\AsTwigFunction;

final class MarqueeTwigFunction
{
    #[AsTwigFunction('marquee_images')]
    public function __invoke(string $image): string
    {
        return DiversUtils::marqueeImages($image);
    }
}
