<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Enums;

/**
 * Static Enum of what each element may not have anywhere inside of it, however deep.
 *
 * As with content models, each entry is the tag name of an element or a category of them.
 * These come from the individual element definitions in the HTML Living Standard, where they
 * read like "phrasing content, but there must be no interactive content descendant".
 *
 * @see https://html.spec.whatwg.org/multipage/semantics.html
 */
class ForbiddenDescendants extends StaticEnum
{

    /**
     * Map of names to the elements and categories forbidden inside of them.
     *
     * @var array
     */
    protected static array $keys = [
        Elements::A        => [Elements::A, Categories::INTERACTIVE],
        Elements::ADDRESS  => [
            Elements::ADDRESS,
            Elements::HEADER,
            Elements::FOOTER,
            Categories::HEADING,
            Categories::SECTIONING,
        ],
        Elements::AUDIO    => [Elements::AUDIO, Elements::VIDEO],
        Elements::BUTTON   => [Categories::INTERACTIVE],
        Elements::CAPTION  => [Elements::TABLE],
        Elements::DFN      => [Elements::DFN],
        Elements::DT       => [Elements::HEADER, Elements::FOOTER, Categories::HEADING, Categories::SECTIONING],
        Elements::FOOTER   => [Elements::HEADER, Elements::FOOTER],
        Elements::FORM     => [Elements::FORM],
        Elements::HEADER   => [Elements::HEADER, Elements::FOOTER],
        Elements::LABEL    => [Elements::LABEL],
        Elements::METER    => [Elements::METER],
        Elements::NOSCRIPT => [Elements::NOSCRIPT],
        Elements::PROGRESS => [Elements::PROGRESS],
        Elements::TH       => [Elements::HEADER, Elements::FOOTER, Categories::HEADING, Categories::SECTIONING],
        Elements::VIDEO    => [Elements::AUDIO, Elements::VIDEO],
    ];
}//end class
