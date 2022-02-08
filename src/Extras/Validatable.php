<?php

namespace Hackdelta\Mpesa\Extras;

use Hackdelta\Mpesa\Exceptions\MpesaInternalException;

/**
 * Contains utility function for performing validation to make sure
 * a certain variable is set or not.
 *
 * Not part of the official files
 */
trait Validatable
{
    public function validateString(string $attribute, string $value): bool
    {
        if (trim($value) === '') {
            throw new MpesaInternalException(
                "'{$attribute}' can not be empty"
            );
        }

        return true;
    }

    public function validateArray(string $attribute, string $value, array $required): bool
    {
        if (trim($value) === '') {
            throw new MpesaInternalException(
                "'{$attribute}' can not be empty"
            );
        }

        if (! in_array($value, $required)) {
            throw new MpesaInternalException(
                sprintf(
                    "Invalid value: '%s' for '%s', expected '%s'",
                    $value,
                    $attribute,
                    implode(' or ', $required)
                )
            );
        }

        return true;
    }

    public function validateInt(string $attribute, int $value, int $min = -1, int $max = -1): bool
    {
        if ($min !== -1 && $value < $min) {
            throw new MpesaInternalException(
                sprintf(
                    "Invalid value: '%s' for '%s', must be greater than or equal to %s",
                    $value,
                    $attribute,
                    $min
                )
            );
        }

        if ($max !== -1 && $value > $max) {
            throw new MpesaInternalException(
                sprintf(
                    "Invalid value: '%s' for '%s', must be less than or equal to %s",
                    $value,
                    $attribute,
                    $max
                )
            );
        }

        return true;
    }
}
