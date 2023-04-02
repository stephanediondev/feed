<?php

namespace App\Helper;

use Symfony\Component\PropertyAccess\PropertyAccess;

final class ExtraFieldsHelper
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_FLOAT = 'float';
    public const TYPE_STRING = 'string';

    public static function getPath(string $path, ?array $fields = [], ?string $type = null): mixed
    {
        $elements = explode('.', $path);

        if (1 === count($elements)) {
            if ($fields && array_key_exists($path, $fields)) {
                return static::resolve($fields[$path], $type);
            }
            return null;
        } else {
            foreach ($elements as $element) {
                $fields = &$fields[$element];
            }

            return static::resolve($fields, $type);
        }
    }

    public static function setPath(string $path, mixed $value, ?array &$fields = []): void
    {
        $elements = explode('.', $path);

        foreach ($elements as &$element) {
            $element = '['.$element.']';
        }

        $path = implode('', $elements);

        if ($value instanceof \DateTime) {
            $value = (array) $value;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($fields, $path, $value);
    }

    private static function resolve(mixed $value, string $type = null): mixed
    {
        switch ($type) {
            case null:
                return $value;

            case self::TYPE_BOOL:
                return (bool) $value;

            case self::TYPE_DATETIME:
                if (true == is_array($value)) {
                    return new \DateTime($value['date'], new \DateTimeZone($value['timezone']));
                } elseif ($value instanceof \DateTime) {
                    return $value;
                } elseif (true == is_string($value)) {
                    return new \DateTime($value);
                }

                // no break
            case self::TYPE_FLOAT:
                return floatval($value);

            case self::TYPE_STRING:
                return strval($value);

            default:
                return null;
        }
    }
}
