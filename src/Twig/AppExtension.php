<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\DiversUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Attribute\AsTwigExtension;

#[AsTwigExtension]
class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('rolling_clouds', [DiversUtils::class, 'rollingClouds'], ['is_safe' => ['html']]),
        ];
    }
}
