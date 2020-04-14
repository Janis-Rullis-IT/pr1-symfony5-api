<?php

namespace App\Helper;

class EnumType
{
    const STATUS_Y = 'y';
    const STATUS_N = 'n';
    const INVALID_ENUM_VALUE = 'Invalid ENUM value.';

    /**
     * #40 Is a valid ENUM value?
     *
     * @param type $value
     * @param type $allowedValues
     */
    public static function isValid($value, $allowedValues = []): bool
    {
        // #40 Use 'y', 'n' if another list hasn't been passed.
        $allowedValues = empty($allowedValues) ? [null, self::STATUS_N, self::STATUS_Y, 0, 1, true, false] : $allowedValues;

        return in_array($value, $allowedValues, true);
    }

    /**
     * #40 ENUM implementation - allow only defined values.
     * https://www.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html#solution-1-mapping-to-varchars
     * `columnDefinition` isn't the best "request changes for this column on each call.".
     * Easiest would be just to throw an exception on set
     * - Other annotation-related reads:
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/annotations-reference.html
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/basic-mapping.html
     * https://stackoverflow.com/a/14934834.
     *
     * @param type $value
     * @param type $allowedValues
     *
     * @throws \InvalidArgumentException
     */
    public static function validate($value, $allowedValues = []): void
    {
        if (!self::isValid($value, $allowedValues)) {
            throw new \InvalidArgumentException("'".$value."' ".self::INVALID_ENUM_VALUE, 1);
        }
    }

    /**
     * #40 Convert the value to defined enum values.
     *
     * @param type $value
     * @param type $allowedValues
     */
    public static function parse($value, $allowedValues = []): string
    {
        self::validate($value, $allowedValues);
        if (in_array($value, [null, self::STATUS_N, self::STATUS_Y], true)) {
            return $value;
        } else {
            return empty($value) ? 'n' : 'y';
        }
    }
}
