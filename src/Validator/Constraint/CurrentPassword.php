<?php

namespace App\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

/**
 * @package App\Validator\Constraint
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class CurrentPassword extends Constraint
{
    public string $message = 'error.currrent_password_invalid';

    public function getTargets(): array|string
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }
}