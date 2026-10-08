<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;

/**
 * The rules about an element's children that a list of what's allowed can't express:
 * how many of each there may be, and what order they come in.
 */
class StructureRules
{

    /**
     * The order that the parts of a table come in.
     *
     * @var array
     */
    const TABLE_ORDER = [
        Elements::CAPTION  => 0,
        Elements::COLGROUP => 1,
        Elements::THEAD    => 2,
        Elements::TBODY    => 3,
        Elements::TR       => 3,
        Elements::TFOOT    => 4,
    ];

    /**
     * The order that the parts of an <audio> or <video> come in. Anything else comes after.
     *
     * @var array
     */
    const MEDIA_ORDER = [
        Elements::SOURCE => 0,
        Elements::TRACK  => 1,
    ];

    /**
     * The order that the parts of a <picture> come in.
     *
     * @var array
     */
    const PICTURE_ORDER = [
        Elements::SOURCE => 0,
        Elements::IMG    => 1,
    ];

    /**
     * The order that the parts of an <html> come in.
     *
     * @var array
     */
    const HTML_ORDER = [
        Elements::HEAD => 0,
        Elements::BODY => 1,
    ];

    /**
     * Finds what is wrong with how an element's children are arranged.
     *
     * @param ElementInterface&ParentNodeInterface $element The element whose children to look at.
     *
     * @return array A list of problems, each one a node and a message: [NodeInterface, string].
     */
    public static function check(ElementInterface&ParentNodeInterface $element): array
    {
        $children = self::elementsIn($element);

        if (null === $children) {
            // Some of the content is markup we haven't parsed, so there's no telling what's in there.
            return [];
        }

        $name = $element->getTagName();

        switch ($name) {
            case Elements::HTML:
                return array_merge(
                    self::atMost(1, Elements::HEAD, $children, $name),
                    self::atMost(1, Elements::BODY, $children, $name),
                    self::inOrder(self::HTML_ORDER, $children, $name)
                );

            case Elements::HEAD:
                return array_merge(
                    self::exactlyOne([Elements::TITLE], '<title>', $children, $element),
                    self::atMost(1, Elements::BASE, $children, $name)
                );

            case Elements::TABLE:
                return array_merge(
                    self::atMost(1, Elements::CAPTION, $children, $name),
                    self::atMost(1, Elements::THEAD, $children, $name),
                    self::atMost(1, Elements::TFOOT, $children, $name),
                    self::inOrder(self::TABLE_ORDER, $children, $name),
                    self::notBoth(Elements::TBODY, Elements::TR, $children, $name)
                );

            case Elements::DETAILS:
                return array_merge(
                    self::exactlyOne([Elements::SUMMARY], '<summary>', $children, $element),
                    self::firstOrLast(Elements::SUMMARY, $children, $name, false)
                );

            case Elements::FIELDSET:
                return array_merge(
                    self::atMost(1, Elements::LEGEND, $children, $name),
                    self::firstOrLast(Elements::LEGEND, $children, $name, false)
                );

            case Elements::FIGURE:
                return array_merge(
                    self::atMost(1, Elements::FIGCAPTION, $children, $name),
                    self::firstOrLast(Elements::FIGCAPTION, $children, $name, true)
                );

            case Elements::PICTURE:
                return array_merge(
                    self::exactlyOne([Elements::IMG], '<img>', $children, $element),
                    self::inOrder(self::PICTURE_ORDER, $children, $name)
                );

            case Elements::HGROUP:
                return self::exactlyOne(Categories::get(Categories::HEADING), 'heading', $children, $element);

            case Elements::AUDIO:
            case Elements::VIDEO:
                return array_merge(
                    self::noneWhen($element, 'src', Elements::SOURCE, $children),
                    self::inOrder(self::MEDIA_ORDER, $children, $name)
                );

            case Elements::COLGROUP:
                return self::noneWhen($element, 'span', Elements::COL, $children);

            case Elements::DL:
                return array_merge(
                    self::notBoth(Elements::DIV, Elements::DT, $children, $name),
                    self::notBoth(Elements::DIV, Elements::DD, $children, $name),
                    self::termsThenDescriptions($children, $element, '/^(?:t+d+)*$/')
                );

            default:
                return [];
        }//end switch
    }//end check()

    /**
     * Finds what is wrong with the terms and descriptions of a <div> that groups them inside of a <dl>.
     *
     * @param ElementInterface&ParentNodeInterface $div A <div> whose parent is a <dl>.
     *
     * @return array A list of problems, each one a node and a message: [NodeInterface, string].
     */
    public static function checkGroup(ElementInterface&ParentNodeInterface $div): array
    {
        $children = self::elementsIn($div);

        if (null === $children) {
            return [];
        }

        return self::termsThenDescriptions($children, $div, '/^t+d+$/');
    }//end checkGroup()

    /**
     * Collects the elements directly inside of a parent, looking through fragments.
     *
     * Scripts and templates may be dotted around almost anywhere without counting towards
     * the structure, so they are left out.
     *
     * @param ParentNodeInterface $parent The node whose children to collect.
     *
     * @return ElementInterface[]|null Null when some of the children are raw, unparsed markup.
     */
    private static function elementsIn(ParentNodeInterface $parent): ?array
    {
        $elements = [];

        foreach ($parent->getChildren() as $child) {
            if ($child instanceof RawHtml) {
                return null;
            }

            if ($child instanceof Fragment) {
                $inner = self::elementsIn($child);

                if (null === $inner) {
                    return null;
                }

                $elements = array_merge($elements, $inner);
            } elseif ($child instanceof ElementInterface
                && false === in_array($child->getTagName(), Categories::get(Categories::SCRIPT_SUPPORTING), true)
            ) {
                $elements[] = $child;
            }
        }

        return $elements;
    }//end elementsIn()

    /**
     * Picks out the elements with a given tag name.
     *
     * @param ElementInterface[] $children The elements to pick from.
     * @param string[]           $names    The tag names to look for.
     *
     * @return ElementInterface[]
     */
    private static function named(array $children, array $names): array
    {
        return array_values(array_filter(
            $children,
            fn (ElementInterface $child) => in_array($child->getTagName(), $names, true)
        ));
    }//end named()

    /**
     * Objects to every element of a kind beyond the number that's allowed.
     *
     * @param integer            $limit    How many are allowed.
     * @param string             $name     The tag name to count.
     * @param ElementInterface[] $children The elements to look through.
     * @param string             $parent   The tag name of their parent.
     *
     * @return array
     */
    private static function atMost(int $limit, string $name, array $children, string $parent): array
    {
        $problems = [];

        foreach (array_slice(self::named($children, [$name]), $limit) as $extra) {
            $problems[] = [$extra, '<'.$parent.'> may only have one <'.$name.'>.'];
        }

        return $problems;
    }//end atMost()

    /**
     * Objects when there isn't exactly one element of a kind.
     *
     * @param string[]           $names    The tag names that count.
     * @param string             $label    What to call one of them.
     * @param ElementInterface[] $children The elements to look through.
     * @param ElementInterface   $parent   Their parent.
     *
     * @return array
     */
    private static function exactlyOne(array $names, string $label, array $children, ElementInterface $parent): array
    {
        $found = self::named($children, $names);

        if ([] === $found) {
            return [[$parent, '<'.$parent->getTagName().'> is missing its '.$label.'.']];
        }

        $problems = [];

        foreach (array_slice($found, 1) as $extra) {
            $problems[] = [$extra, '<'.$parent->getTagName().'> may only have one '.$label.'.'];
        }

        return $problems;
    }//end exactlyOne()

    /**
     * Objects to each element that comes after one it should have come before.
     *
     * @param array              $order    Tag names, each with its place in the order. Other elements come last.
     * @param ElementInterface[] $children The elements to look through.
     * @param string             $parent   The tag name of their parent.
     *
     * @return array
     */
    private static function inOrder(array $order, array $children, string $parent): array
    {
        $problems = [];
        $latest   = null;
        $reached  = null;

        foreach ($children as $child) {
            $place = ($order[$child->getTagName()] ?? count($order));

            if (null !== $reached && $place < $reached) {
                $problems[] = [
                    $child,
                    '<'.$child->getTagName().'> must come before <'.$latest.'> inside of <'.$parent.'>.',
                ];
                continue;
            }

            if (null === $reached || $place > $reached) {
                $reached = $place;
                $latest  = $child->getTagName();
            }
        }

        return $problems;
    }//end inOrder()

    /**
     * Objects to an element that isn't the first of its siblings (or, where allowed, the last).
     *
     * @param string             $name      The tag name it concerns.
     * @param ElementInterface[] $children  The elements to look through.
     * @param string             $parent    The tag name of their parent.
     * @param boolean            $orLast    Whether being last is as good as being first.
     *
     * @return array
     */
    private static function firstOrLast(string $name, array $children, string $parent, bool $orLast): array
    {
        $found = self::named($children, [$name]);

        if ([] === $found || $found[0] === $children[0]) {
            return [];
        }

        if (true === $orLast && $found[0] === end($children)) {
            return [];
        }

        $where = (true === $orLast) ? 'the first or the last element' : 'the first element';

        return [[$found[0], '<'.$name.'> must be '.$where.' inside of <'.$parent.'>.']];
    }//end firstOrLast()

    /**
     * Objects when two kinds of element that can't be mixed are both present.
     *
     * @param string             $one      A tag name.
     * @param string             $other    Another tag name.
     * @param ElementInterface[] $children The elements to look through.
     * @param string             $parent   The tag name of their parent.
     *
     * @return array
     */
    private static function notBoth(string $one, string $other, array $children, string $parent): array
    {
        $others = self::named($children, [$other]);

        if ([] === self::named($children, [$one]) || [] === $others) {
            return [];
        }

        return [[$others[0], '<'.$parent.'> may have <'.$one.'> or <'.$other.'> elements, but not both.']];
    }//end notBoth()

    /**
     * Objects to every element of a kind when the parent has an attribute that takes their place.
     *
     * @param ElementInterface   $parent    The parent element.
     * @param string             $attribute The attribute that makes them redundant.
     * @param string             $name      The tag name it concerns.
     * @param ElementInterface[] $children  The elements to look through.
     *
     * @return array
     */
    private static function noneWhen(ElementInterface $parent, string $attribute, string $name, array $children): array
    {
        if (false === $parent->hasAttribute($attribute)) {
            return [];
        }

        $problems = [];

        foreach (self::named($children, [$name]) as $child) {
            $problems[] = [
                $child,
                '<'.$parent->getTagName().'> has a "'.$attribute.'" attribute, so it may not have <'.$name.'> elements.',
            ];
        }

        return $problems;
    }//end noneWhen()

    /**
     * Objects when terms and descriptions don't come in name-value groups.
     *
     * @param ElementInterface[] $children The elements to look through.
     * @param ElementInterface   $parent   Their parent.
     * @param string             $pattern  What the run of terms ("t") and descriptions ("d") has to look like.
     *
     * @return array
     */
    private static function termsThenDescriptions(array $children, ElementInterface $parent, string $pattern): array
    {
        $run = '';

        foreach (self::named($children, [Elements::DT, Elements::DD]) as $child) {
            $run .= (Elements::DT === $child->getTagName()) ? 't' : 'd';
        }

        if (1 === preg_match($pattern, $run)) {
            return [];
        }

        return [
            [
                $parent,
                'Inside of <'.$parent->getTagName().'>, every group must be one or more <dt> followed by one or more <dd>.',
            ],
        ];
    }//end termsThenDescriptions()
}//end class
