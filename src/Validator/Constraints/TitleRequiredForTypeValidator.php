<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Not implemented.
 *
 * use App\Validator\Constraints as AppAssert;
 * #[AppAssert\TitleRequiredForType]
 * class Post
 */
class TitleRequiredForTypeValidator extends ConstraintValidator
{
    public function validate(mixed $object, Constraint $constraint): void
    {
        if (!$constraint instanceof TitleRequiredForType) {
            throw new UnexpectedTypeException($constraint, TitleRequiredForType::class);
        }

        if (!\is_object($object)) {
            throw new UnexpectedValueException($object, 'object');
        }

        // Ensure the object has getType() and getTitle() methods
        if (method_exists($object, 'getType') && method_exists($object, 'getTitle')) {
            $type = $object->getType();
            $title = $object->getTitle();

            if (!\in_array($type->value, [3, 4], true) && '' === $title) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('title')
                    ->addViolation();
            }
        }
    }
}
