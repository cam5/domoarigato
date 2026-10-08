<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Enums;

/**
 * Static Enum of the kinds of content that HTML sorts its elements into.
 *
 * Each category maps to the elements that always belong to it. These are what content models
 * are written in terms of: a <p> may hold "phrasing" content, a <ul> may not.
 *
 * @see https://html.spec.whatwg.org/multipage/indices.html#element-content-categories
 */
class Categories extends StaticEnum
{
    const METADATA          = 'metadata';
    const FLOW              = 'flow';
    const SECTIONING        = 'sectioning';
    const HEADING           = 'heading';
    const PHRASING          = 'phrasing';
    const EMBEDDED          = 'embedded';
    const INTERACTIVE       = 'interactive';
    const PALPABLE          = 'palpable';
    const SCRIPT_SUPPORTING = 'script-supporting';

    /**
     * Map of categories to the names of the elements in them.
     *
     * @var array
     */
    protected static array $keys = [
        self::METADATA => [
            'base', 'link', 'meta', 'noscript', 'script', 'style', 'template', 'title',
        ],
        self::FLOW => [
            'a', 'abbr', 'address', 'article', 'aside', 'audio', 'b', 'bdi', 'bdo', 'blockquote', 'br', 'button',
            'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'em',
            'embed', 'fieldset', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header',
            'hgroup', 'hr', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label', 'map', 'mark', 'math', 'menu',
            'meter', 'nav', 'noscript', 'object', 'ol', 'output', 'p', 'picture', 'pre', 'progress', 'q', 'ruby',
            's', 'samp', 'script', 'search', 'section', 'select', 'slot', 'small', 'span', 'strong', 'sub', 'sup',
            'svg', 'table', 'template', 'textarea', 'time', 'u', 'ul', 'var', 'video', 'wbr',
        ],
        self::SECTIONING => [
            'article', 'aside', 'nav', 'section',
        ],
        self::HEADING => [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup',
        ],
        self::PHRASING => [
            'a', 'abbr', 'audio', 'b', 'bdi', 'bdo', 'br', 'button', 'canvas', 'cite', 'code', 'data', 'datalist',
            'del', 'dfn', 'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label', 'map', 'mark',
            'math', 'meter', 'noscript', 'object', 'output', 'picture', 'progress', 'q', 'ruby', 's', 'samp',
            'script', 'select', 'selectedcontent', 'slot', 'small', 'span', 'strong', 'sub', 'sup', 'svg',
            'template', 'textarea', 'time', 'u', 'var', 'video', 'wbr',
        ],
        self::EMBEDDED => [
            'audio', 'canvas', 'embed', 'iframe', 'img', 'math', 'object', 'picture', 'svg', 'video',
        ],
        self::INTERACTIVE => [
            'button', 'details', 'embed', 'iframe', 'label', 'select', 'textarea',
        ],
        self::PALPABLE => [
            'a', 'abbr', 'address', 'article', 'aside', 'b', 'bdi', 'bdo', 'blockquote', 'button', 'canvas',
            'cite', 'code', 'data', 'del', 'details', 'dfn', 'div', 'em', 'embed', 'fieldset', 'figure', 'footer',
            'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'i', 'iframe', 'img', 'ins', 'kbd',
            'label', 'main', 'map', 'mark', 'math', 'meter', 'nav', 'object', 'output', 'p', 'picture', 'pre',
            'progress', 'q', 'ruby', 's', 'samp', 'search', 'section', 'select', 'small', 'span', 'strong', 'sub',
            'sup', 'svg', 'table', 'textarea', 'time', 'u', 'var', 'video',
        ],
        self::SCRIPT_SUPPORTING => [
            'script', 'template',
        ],
    ];

    /**
     * Map of categories to the elements that only belong to them some of the time.
     *
     * An <a> is interactive when it has an "href", a <meta> is flow content when it has an
     * "itemprop", and so on. Deciding takes an actual element, which a name alone can't give.
     *
     * @var array
     */
    protected static array $conditional = [
        self::FLOW              => ['area', 'link', 'main', 'meta'],
        self::PHRASING          => ['area', 'link', 'meta'],
        self::INTERACTIVE       => ['a', 'audio', 'img', 'input', 'video'],
        self::PALPABLE          => ['audio', 'dl', 'input', 'menu', 'ol', 'ul'],
    ];

    /**
     * Retrieves the categories that an element always belongs to.
     *
     * @param string $name The tag name of the element.
     *
     * @return string[]
     */
    public static function of(string $name): array
    {
        return self::containing(static::$keys, $name);
    }//end of()

    /**
     * Retrieves the categories that an element belongs to only under certain conditions.
     *
     * @param string $name The tag name of the element.
     *
     * @return string[]
     */
    public static function conditionallyOf(string $name): array
    {
        return self::containing(static::$conditional, $name);
    }//end conditionallyOf()

    /**
     * Finds the categories in a map whose list of elements has a given name in it.
     *
     * @param array  $map  Categories, each with a list of element names.
     * @param string $name The tag name of the element.
     *
     * @return string[]
     */
    private static function containing(array $map, string $name): array
    {
        $name       = strtolower($name);
        $categories = [];

        foreach ($map as $category => $names) {
            if (true === in_array($name, $names, true)) {
                $categories[] = $category;
            }
        }

        return $categories;
    }//end containing()
}//end class
