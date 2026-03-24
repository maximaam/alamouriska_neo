<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;

final class EntityTypeTwigFunction
{
    #[AsTwigFunction('entity_type')]
    public function __invoke(array $object): string
    {
        return isset($object['descriptionHtml']) ? 'wall' : 'post';
    }
}
