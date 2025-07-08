<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Post;
use App\Entity\Wall;
use App\Utils\DiversUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('rolling_clouds', DiversUtils::rollingClouds(...), ['is_safe' => ['html']]),
            new TwigFunction('instanceof', $this->isInstanceof(...)),
        ];
    }

    public function isInstanceof(Post|Wall $object, string $class): bool
    {
        return $object instanceof $class;
    }
}
