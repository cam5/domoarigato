<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Enums;

/**
 * Generic static Enum class.
 */
class StaticEnum
{

    /**
     * The list of keys to reference.
     *
     * @var array
     */
    protected static array $keys = [];

    /**
     * Retrieves a given key from the enum, when present.
     *
     * @param string $string The key we're searching for in the enum.
     *
     * @throws \LogicException When a key outside the defined set is used.
     *
     * @return mixed The value associated with that key.
     */
    public static function get(string $string): mixed
    {
        if (true === static::contains($string)) {
            return static::$keys[strtolower($string)];
        }

        throw new \LogicException('Could not find "'.$string.'" key in '.static::class.' enum.');
    }//end get()

    /**
     * Retrieves every key in the enum, along with its value.
     *
     * @return array
     */
    public static function all(): array
    {
        return static::$keys;
    }//end all()

    /**
     * Checks that a given key is in the set.
     *
     * HTML names are case-insensitive, and so is the lookup.
     *
     * @param string $string The key we're searching for in the enum.
     *
     * @return boolean
     */
    public static function contains(string $string): bool
    {
        return array_key_exists(strtolower($string), static::$keys);
    }//end contains()
}//end class
