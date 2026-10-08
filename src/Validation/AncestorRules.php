<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Enums\ForbiddenDescendants;

/**
 * The rules about where an element may sit that reach further up than its parent.
 */
class AncestorRules
{

    /**
     * The only elements of HTML that a <main> may be inside of.
     *
     * @var string[]
     */
    const AROUND_MAIN = [Elements::HTML, Elements::BODY, Elements::DIV, Elements::FORM];

    /**
     * Explains why an element may not sit beneath the ancestors it has, if indeed it may not.
     *
     * @param ElementInterface   $element   The element in question.
     * @param ElementInterface[] $ancestors The elements it sits inside of, outermost first.
     *
     * @return string|null Null when the element is fine where it is.
     */
    public static function problemWith(ElementInterface $element, array $ancestors): ?string
    {
        $name  = $element->getTagName();
        $names = array_map(fn (ElementInterface $ancestor) => $ancestor->getTagName(), $ancestors);

        foreach (array_reverse($names) as $ancestor) {
            $reason = self::forbiddenBy($element, $ancestor);

            if (null !== $reason) {
                return $reason;
            }

            if (Elements::MAIN === $name
                && true === Elements::contains($ancestor)
                && false === in_array($ancestor, self::AROUND_MAIN, true)
            ) {
                return '<main> is not allowed anywhere inside of <'.$ancestor.'>. '
                    .'It may only be inside of <html>, <body>, <div>, <form> and custom elements.';
            }
        }

        if (Elements::AREA === $name && [] !== $names && false === in_array(Elements::MAP, $names, true)) {
            return '<area> is only allowed somewhere inside of a <map>.';
        }

        return null;
    }//end problemWith()

    /**
     * Explains why an ancestor rules an element out, if it does.
     *
     * @param ElementInterface $element  The element in question.
     * @param string           $ancestor The tag name of one of the elements it sits inside of.
     *
     * @return string|null Null when that ancestor has no objection.
     */
    private static function forbiddenBy(ElementInterface $element, string $ancestor): ?string
    {
        if (false === ForbiddenDescendants::contains($ancestor)) {
            return null;
        }

        $name      = $element->getTagName();
        $forbidden = ForbiddenDescendants::get($ancestor);

        if (true === in_array($name, $forbidden, true)) {
            return '<'.$name.'> is not allowed anywhere inside of <'.$ancestor.'>.';
        }

        $categories = array_intersect(Categorizer::categoriesOf($element), $forbidden);

        if ([] !== $categories) {
            return '<'.$name.'> is '.reset($categories).' content, which is not allowed anywhere inside of <'.$ancestor.'>.';
        }

        return null;
    }//end forbiddenBy()
}//end class
