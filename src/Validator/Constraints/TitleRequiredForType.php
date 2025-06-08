<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TitleRequiredForType extends Constraint
{
    public string $message = 'Title is required unless type is 3 or 4.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
