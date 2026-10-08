<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Enums;

/**
 * Static Enum of what each element may have directly inside of it.
 *
 * A content model is a list. Each entry is the tag name of an element that is allowed, a category
 * from the `Categories` enum whose elements are all allowed, or one of the markers below.
 * An empty list means that nothing is allowed at all.
 *
 * @see https://html.spec.whatwg.org/multipage/indices.html#elements-3
 */
class ContentModels extends StaticEnum
{

    /**
     * Marker: text is allowed. (The flow and phrasing categories include text as well.)
     *
     * @var string
     */
    const TEXT = '#text';

    /**
     * Marker: whatever the element's own parent allows, this element allows too.
     *
     * @var string
     */
    const TRANSPARENT = '#transparent';

    /**
     * Marker: the content follows rules from outside of HTML, and is left alone.
     *
     * @var string
     */
    const ANYTHING = '#anything';

    /**
     * Map of names to content models.
     *
     * @var array
     */
    protected static array $keys = [
        Elements::A               => [self::TRANSPARENT],
        Elements::ABBR            => [Categories::PHRASING],
        Elements::ADDRESS         => [Categories::FLOW],
        Elements::AREA            => [],
        Elements::ARTICLE         => [Categories::FLOW],
        Elements::ASIDE           => [Categories::FLOW],
        Elements::AUDIO           => ['source', 'track', self::TRANSPARENT],
        Elements::B               => [Categories::PHRASING],
        Elements::BASE            => [],
        Elements::BDI             => [Categories::PHRASING],
        Elements::BDO             => [Categories::PHRASING],
        Elements::BLOCKQUOTE      => [Categories::FLOW],
        Elements::BODY            => [Categories::FLOW],
        Elements::BR              => [],
        Elements::BUTTON          => [Categories::PHRASING],
        Elements::CANVAS          => [self::TRANSPARENT],
        Elements::CAPTION         => [Categories::FLOW],
        Elements::CITE            => [Categories::PHRASING],
        Elements::CODE            => [Categories::PHRASING],
        Elements::COL             => [],
        Elements::COLGROUP        => ['col', 'template'],
        Elements::DATA            => [Categories::PHRASING],
        Elements::DATALIST        => [Categories::PHRASING, 'option', Categories::SCRIPT_SUPPORTING],
        Elements::DD              => [Categories::FLOW],
        Elements::DEL             => [self::TRANSPARENT],
        Elements::DETAILS         => ['summary', Categories::FLOW],
        Elements::DFN             => [Categories::PHRASING],
        Elements::DIALOG          => [Categories::FLOW],
        Elements::DIV             => [Categories::FLOW],
        Elements::DL              => ['dt', 'dd', 'div', Categories::SCRIPT_SUPPORTING],
        Elements::DT              => [Categories::FLOW],
        Elements::EM              => [Categories::PHRASING],
        Elements::EMBED           => [],
        Elements::FIELDSET        => ['legend', Categories::FLOW],
        Elements::FIGCAPTION      => [Categories::FLOW],
        Elements::FIGURE          => ['figcaption', Categories::FLOW],
        Elements::FOOTER          => [Categories::FLOW],
        Elements::FORM            => [Categories::FLOW],
        Elements::H1              => [Categories::PHRASING],
        Elements::H2              => [Categories::PHRASING],
        Elements::H3              => [Categories::PHRASING],
        Elements::H4              => [Categories::PHRASING],
        Elements::H5              => [Categories::PHRASING],
        Elements::H6              => [Categories::PHRASING],
        Elements::HEAD            => [Categories::METADATA],
        Elements::HEADER          => [Categories::FLOW],
        Elements::HGROUP          => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', Categories::SCRIPT_SUPPORTING],
        Elements::HR              => [],
        Elements::HTML            => ['head', 'body'],
        Elements::I               => [Categories::PHRASING],
        Elements::IFRAME          => [],
        Elements::IMG             => [],
        Elements::INPUT           => [],
        Elements::INS             => [self::TRANSPARENT],
        Elements::KBD             => [Categories::PHRASING],
        Elements::LABEL           => [Categories::PHRASING],
        Elements::LEGEND          => [Categories::PHRASING, Categories::HEADING],
        Elements::LI              => [Categories::FLOW],
        Elements::LINK            => [],
        Elements::MAIN            => [Categories::FLOW],
        Elements::MAP             => [self::TRANSPARENT, 'area'],
        Elements::MARK            => [Categories::PHRASING],
        Elements::MATH            => [self::ANYTHING],
        Elements::MENU            => ['li', Categories::SCRIPT_SUPPORTING],
        Elements::META            => [],
        Elements::METER           => [Categories::PHRASING],
        Elements::NAV             => [Categories::FLOW],
        Elements::NOSCRIPT        => [self::TRANSPARENT],
        Elements::OBJECT          => [self::TRANSPARENT],
        Elements::OL              => ['li', Categories::SCRIPT_SUPPORTING],
        Elements::OPTGROUP        => ['option', Categories::SCRIPT_SUPPORTING, 'noscript', 'div', 'legend'],
        Elements::OPTION          => [self::TEXT, 'div', Categories::PHRASING],
        Elements::OUTPUT          => [Categories::PHRASING],
        Elements::P               => [Categories::PHRASING],
        Elements::PICTURE         => ['source', 'img', Categories::SCRIPT_SUPPORTING],
        Elements::PRE             => [Categories::PHRASING],
        Elements::PROGRESS        => [Categories::PHRASING],
        Elements::Q               => [Categories::PHRASING],
        Elements::RP              => [self::TEXT],
        Elements::RT              => [Categories::PHRASING],
        Elements::RUBY            => [Categories::PHRASING, 'rt', 'rp'],
        Elements::S               => [Categories::PHRASING],
        Elements::SAMP            => [Categories::PHRASING],
        Elements::SCRIPT          => [self::TEXT],
        Elements::SEARCH          => [Categories::FLOW],
        Elements::SECTION         => [Categories::FLOW],
        Elements::SELECT          => [
            'option',
            'optgroup',
            'hr',
            Categories::SCRIPT_SUPPORTING,
            'noscript',
            'div',
            'button',
        ],
        Elements::SELECTEDCONTENT => [],
        Elements::SLOT            => [self::TRANSPARENT],
        Elements::SMALL           => [Categories::PHRASING],
        Elements::SOURCE          => [],
        Elements::SPAN            => [Categories::PHRASING],
        Elements::STRONG          => [Categories::PHRASING],
        Elements::STYLE           => [self::TEXT],
        Elements::SUB             => [Categories::PHRASING],
        Elements::SUMMARY         => [Categories::PHRASING, Categories::HEADING],
        Elements::SUP             => [Categories::PHRASING],
        Elements::SVG             => [self::ANYTHING],
        Elements::TABLE           => [
            'caption',
            'colgroup',
            'thead',
            'tbody',
            'tfoot',
            'tr',
            Categories::SCRIPT_SUPPORTING,
        ],
        Elements::TBODY           => ['tr', Categories::SCRIPT_SUPPORTING],
        Elements::TD              => [Categories::FLOW],
        // The spec gives <template> no content of its own, since its children live in a separate fragment
        // where anything goes. Here they are simply its children, so anything goes for them.
        Elements::TEMPLATE        => [self::ANYTHING],
        Elements::TEXTAREA        => [self::TEXT],
        Elements::TFOOT           => ['tr', Categories::SCRIPT_SUPPORTING],
        Elements::TH              => [Categories::FLOW],
        Elements::THEAD           => ['tr', Categories::SCRIPT_SUPPORTING],
        Elements::TIME            => [Categories::PHRASING],
        Elements::TITLE           => [self::TEXT],
        Elements::TR              => ['th', 'td', Categories::SCRIPT_SUPPORTING],
        Elements::TRACK           => [],
        Elements::U               => [Categories::PHRASING],
        Elements::UL              => ['li', Categories::SCRIPT_SUPPORTING],
        Elements::VAR             => [Categories::PHRASING],
        Elements::VIDEO           => ['source', 'track', self::TRANSPARENT],
        Elements::WBR             => [],
    ];
}//end class
