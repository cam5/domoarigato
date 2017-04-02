<?php

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
    protected static $keys = [];

    /**
     * Retrieves a given key from the enum, when present.
     *
     * @param string $string The key we're searching for in the enum.
     *
     * @throws \LogicException When a key outside the defined set is used.
     *
     * @return string The value associated with that key.
     */
    public static function get(string $string)
    {
        if (true === self::contains($string)) {
            return static::$keys[$string];
        }

        throw new \LogicException('Could not find "'.$string.'" key in '.self::class.' enum.');
    }//end get()

    /**
     * Checks that a given key is in the set.
     *
     * @param string $string The key we're searching for in the enum.
     *
     * @return boolean
     */
    public static function contains(string $string)
    {
        return key_exists($string, static::$keys);
    }//end contains()
}//end class
