<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Post;
use App\Entity\Wall;
use Twig\Attribute\AsTwigFunction;

final class InstanceofTwigFunction
{
    #[AsTwigFunction('instanceof')]
    public function __invoke(Post|Wall $object, string $class): bool
    {
        return $object instanceof $class;
    }
}
