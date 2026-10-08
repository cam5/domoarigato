<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Support;

/**
 * Low-level helpers for producing safe, well-formed HTML fragments.
 */
class Html
{

    /**
     * Characters that may never appear in an attribute name.
     *
     * Controls, whitespace, quotes, ">", "/", "=" and noncharacters come straight from the
     * HTML syntax; "<" is tolerated by parsers but is always a mistake, so it is refused too.
     *
     * @var string
     */
    const INVALID_ATTRIBUTE_NAME_PATTERN = '/['
        .'\x{0}-\x{20}\x{7F}-\x{9F}"\'<>\/=\x{FDD0}-\x{FDEF}'
        .'\x{FFFE}\x{FFFF}'
        .'\x{1FFFE}\x{1FFFF}'
        .'\x{2FFFE}\x{2FFFF}'
        .'\x{3FFFE}\x{3FFFF}'
        .'\x{4FFFE}\x{4FFFF}'
        .'\x{5FFFE}\x{5FFFF}'
        .'\x{6FFFE}\x{6FFFF}'
        .'\x{7FFFE}\x{7FFFF}'
        .'\x{8FFFE}\x{8FFFF}'
        .'\x{9FFFE}\x{9FFFF}'
        .'\x{AFFFE}\x{AFFFF}'
        .'\x{BFFFE}\x{BFFFF}'
        .'\x{CFFFE}\x{CFFFF}'
        .'\x{DFFFE}\x{DFFFF}'
        .'\x{EFFFE}\x{EFFFF}'
        .'\x{FFFFE}\x{FFFFF}'
        .'\x{10FFFE}\x{10FFFF}'
        .']/u';

    /**
     * Escapes a string for use inside a double-quoted attribute value.
     *
     * @param string $string The raw value.
     *
     * @return string
     */
    public static function escapeAttribute(string $string): string
    {
        return htmlspecialchars($string, (ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5), 'UTF-8');
    }//end escapeAttribute()

    /**
     * Normalizes an attribute name, refusing anything that could not be serialized safely.
     *
     * Attribute names are ASCII case-insensitive in HTML, so they are lowercased here.
     *
     * @param string $name The name of the attribute.
     *
     * @throws \InvalidArgumentException When the name is empty or contains forbidden characters.
     *
     * @return string The normalized name.
     */
    public static function normalizeAttributeName(string $name): string
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('An attribute name cannot be empty.');
        }

        // A failed match (false) means the name was not valid UTF-8.
        if (0 !== preg_match(self::INVALID_ATTRIBUTE_NAME_PATTERN, $name)) {
            throw new \InvalidArgumentException('"'.$name.'" is not a valid attribute name.');
        }

        return strtolower($name);
    }//end normalizeAttributeName()
}//end class
