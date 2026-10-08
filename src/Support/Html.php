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
     * What a tag name may look like: a built-in element's letters and digits, or a custom
     * element's wider set of characters.
     *
     * @var string
     */
    const TAG_NAME_PATTERN = '/^[A-Za-z]['
        .'A-Za-z0-9._\-\x{B7}\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{37D}\x{37F}-\x{1FFF}\x{200C}\x{200D}'
        .'\x{203F}\x{2040}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}'
        .'\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}'
        .']*$/Du';

    /**
     * Escapes a string for use as text between tags.
     *
     * Quotes mean nothing there, so they are left alone.
     *
     * @param string $string The raw text.
     *
     * @return string
     */
    public static function escapeText(string $string): string
    {
        return htmlspecialchars($string, (ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5), 'UTF-8');
    }//end escapeText()

    /**
     * Normalizes a tag name, refusing anything that could not be serialized safely.
     *
     * Tag names are ASCII case-insensitive in HTML, so they are lowercased here.
     *
     * @param string $name The name of the element.
     *
     * @throws \InvalidArgumentException When the name isn't a valid tag name.
     *
     * @return string The normalized name.
     */
    public static function normalizeTagName(string $name): string
    {
        if (1 !== preg_match(self::TAG_NAME_PATTERN, $name)) {
            throw new \InvalidArgumentException('"'.$name.'" is not a valid tag name.');
        }

        return strtolower($name);
    }//end normalizeTagName()

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
