<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;

/**
 * Works out which categories of content an actual element belongs to.
 *
 * The `Categories` enum can answer for a tag name. Some answers depend on more than the name,
 * such as which attributes are set, and those are settled here.
 */
class Categorizer
{

    /**
     * The values of "rel" that let a <link> sit in the body of a document.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#body-ok
     *
     * @var string[]
     */
    const BODY_OK = ['dns-prefetch', 'modulepreload', 'pingback', 'preconnect', 'prefetch', 'preload', 'stylesheet'];

    /**
     * Retrieves the categories that an element belongs to, as it stands right now.
     *
     * Elements that aren't part of HTML are taken to be custom elements, which may go wherever
     * flow or phrasing content may.
     *
     * @param ElementInterface $element The element to categorize.
     *
     * @return string[]
     */
    public static function categoriesOf(ElementInterface $element): array
    {
        $name = $element->getTagName();

        if (false === Elements::contains($name)) {
            return [Categories::FLOW, Categories::PHRASING, Categories::PALPABLE];
        }

        $categories = Categories::of($name);

        foreach (Categories::conditionallyOf($name) as $category) {
            if (true === self::qualifies($element)) {
                $categories[] = $category;
            }
        }

        return $categories;
    }//end categoriesOf()

    /**
     * Decides whether an element meets the condition for the categories it only sometimes belongs to.
     *
     * Each of these elements has a single condition, whichever of its categories is being asked about.
     *
     * @param ElementInterface $element The element in question.
     *
     * @return boolean
     */
    private static function qualifies(ElementInterface $element): bool
    {
        switch ($element->getTagName()) {
            case Elements::A:
                return $element->hasAttribute('href');

            case Elements::AUDIO:
            case Elements::VIDEO:
                return $element->hasAttribute('controls');

            case Elements::IMG:
                return $element->hasAttribute('usemap');

            case Elements::INPUT:
                return (false === self::isHiddenInput($element));

            case Elements::LINK:
                return (true === $element->hasAttribute('itemprop') || true === self::isBodyOk($element));

            case Elements::DL:
                return self::hasChildNamed($element, [Elements::DT, Elements::DD, Elements::DIV]);

            case Elements::MENU:
            case Elements::OL:
            case Elements::UL:
                return self::hasChildNamed($element, [Elements::LI]);

            case Elements::META:
                return $element->hasAttribute('itemprop');

            default:
                // What's left are <area> and <main>, which depend on where they are rather than
                // on what they are. Whether they're in the right place is for the validator to say.
                return true;
        }//end switch
    }//end qualifies()

    /**
     * Checks for an <input type="hidden">.
     *
     * @param ElementInterface $element An <input> element.
     *
     * @return boolean
     */
    private static function isHiddenInput(ElementInterface $element): bool
    {
        $type = $element->getAttribute('type');

        return (null !== $type && 'hidden' === strtolower((string) $type->getValue()));
    }//end isHiddenInput()

    /**
     * Checks whether every keyword in a <link>'s "rel" is one that's allowed in the body.
     *
     * @param ElementInterface $element A <link> element.
     *
     * @return boolean
     */
    private static function isBodyOk(ElementInterface $element): bool
    {
        $rel = $element->getAttribute('rel');

        if (false === ($rel instanceof CollectionAttribute) || true === $rel->isEmpty()) {
            return false;
        }

        $keywords = array_map('strtolower', $rel->getValues());

        return ([] === array_diff($keywords, self::BODY_OK));
    }//end isBodyOk()

    /**
     * Checks whether an element has a child element with one of the given names.
     *
     * @param ElementInterface $element The parent element.
     * @param string[]         $names   The tag names to look for.
     *
     * @return boolean
     */
    private static function hasChildNamed(ElementInterface $element, array $names): bool
    {
        if ($element instanceof ParentNodeInterface) {
            foreach ($element->getChildren() as $child) {
                if ($child instanceof ElementInterface && true === in_array($child->getTagName(), $names, true)) {
                    return true;
                }
            }
        }

        return false;
    }//end hasChildNamed()
}//end class
