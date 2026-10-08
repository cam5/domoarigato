<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\ContentModels;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Doctype;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;

/**
 * Checks a tree of nodes against HTML's rules for what may go inside of what.
 *
 * Three kinds of rule are applied: what each element may hold directly (`ContentModels`), what
 * it may not have anywhere beneath it (`AncestorRules`), and how many of its children there
 * may be and in what order (`StructureRules`).
 *
 * Markup that was passed in as a string (`RawHtml`) isn't parsed, and so isn't checked. Neither
 * is anything inside of an <svg>, a <math> or a <template>, where HTML's rules don't apply.
 */
class Validator
{

    /**
     * Finds everything in a tree of nodes that breaks the rules.
     *
     * @param NodeInterface $root The node at the top of the tree.
     *
     * @return Violation[] Empty when the tree is valid.
     */
    public static function validate(NodeInterface $root): array
    {
        $violations = [];

        if ($root instanceof ElementInterface) {
            self::checkElement($root, [], $violations);
        } elseif ($root instanceof Fragment) {
            self::checkChildren($root, [], null, $violations);
        }

        return $violations;
    }//end validate()

    /**
     * Makes sure that a tree of nodes is valid.
     *
     * @param NodeInterface $root The node at the top of the tree.
     *
     * @throws InvalidContentException When it isn't.
     *
     * @return void
     */
    public static function assertValid(NodeInterface $root): void
    {
        $violations = self::validate($root);

        if ([] !== $violations) {
            throw new InvalidContentException($violations);
        }
    }//end assertValid()

    /**
     * Checks an element's children, and theirs in turn.
     *
     * @param ElementInterface   $element    The element to check.
     * @param ElementInterface[] $ancestors  The elements it sits inside of, outermost first.
     * @param Violation[]        $violations The list that problems are added to.
     *
     * @return void
     */
    private static function checkElement(ElementInterface $element, array $ancestors, array &$violations): void
    {
        if (false === ($element instanceof ParentNodeInterface)) {
            return;
        }

        $model = self::contentModel($element, $ancestors);

        if (null !== $model && true === in_array(ContentModels::ANYTHING, $model, true)) {
            return;
        }

        $parent    = end($ancestors);
        $isGroup   = (false !== $parent && Elements::DIV === $element->getTagName() && Elements::DL === $parent->getTagName());
        $problems  = (true === $isGroup) ? StructureRules::checkGroup($element) : StructureRules::check($element);
        $inside    = array_merge($ancestors, [$element]);

        foreach ($problems as [$node, $problem]) {
            $path = ($node === $element) ? self::path($ancestors, $node) : self::path($inside, $node);

            $violations[] = new Violation($node, $path, $problem);
        }

        self::checkChildren($element, $inside, $model, $violations);
    }//end checkElement()

    /**
     * Checks each of the nodes inside of a parent against the parent's content model.
     *
     * A fragment has no tag of its own, so its children are checked as though they were in its place.
     *
     * @param ParentNodeInterface $parent     The node whose children to check.
     * @param ElementInterface[]  $ancestors  The elements the children sit inside of, outermost first.
     * @param string[]|null       $model      What may go here. Null when there's no way of knowing.
     * @param Violation[]         $violations The list that problems are added to.
     *
     * @return void
     */
    private static function checkChildren(
        ParentNodeInterface $parent,
        array $ancestors,
        ?array $model,
        array &$violations
    ): void {
        foreach ($parent->getChildren() as $child) {
            if ($child instanceof Fragment) {
                self::checkChildren($child, $ancestors, $model, $violations);
                continue;
            }

            $problem = self::problemWith($child, $ancestors, $model);

            if (null !== $problem) {
                $violations[] = new Violation($child, self::path($ancestors, $child), $problem);
            }

            if ($child instanceof ElementInterface) {
                $problem = AncestorRules::problemWith($child, $ancestors);

                if (null !== $problem) {
                    $violations[] = new Violation($child, self::path($ancestors, $child), $problem);
                }

                self::checkElement($child, $ancestors, $violations);
            }
        }
    }//end checkChildren()

    /**
     * Explains why a node may not sit where it does, if indeed it may not.
     *
     * @param NodeInterface      $child     The node in question.
     * @param ElementInterface[] $ancestors The elements it sits inside of, outermost first.
     * @param string[]|null      $model     What may go here. Null when there's no way of knowing.
     *
     * @return string|null Null when the node is fine where it is.
     */
    private static function problemWith(NodeInterface $child, array $ancestors, ?array $model): ?string
    {
        if ($child instanceof Comment || $child instanceof RawHtml) {
            return null;
        }

        if ($child instanceof Doctype) {
            return ([] === $ancestors) ? null : 'A doctype belongs at the very top of a document, not inside of an element.';
        }

        if (null === $model) {
            return null;
        }

        $parent = '<'.end($ancestors)->getTagName().'>';

        if ($child instanceof Text) {
            if (true === self::allowsText($model) || '' === trim($child->getTextContent(), " \t\n\f\r")) {
                return null;
            }

            return 'Text is not allowed directly inside of '.$parent.'. '.self::describe($model);
        }

        if (true === self::allowsElement($model, $child)) {
            return null;
        }

        return '<'.$child->getTagName().'> is not allowed directly inside of '.$parent.'. '.self::describe($model);
    }//end problemWith()

    /**
     * Works out what an element may contain, given where it is.
     *
     * @param ElementInterface   $element   The element in question.
     * @param ElementInterface[] $ancestors The elements it sits inside of, outermost first.
     *
     * @return string[]|null Null when there's no way of knowing: a transparent element with no parent to go by.
     */
    private static function contentModel(ElementInterface $element, array $ancestors): ?array
    {
        $name   = $element->getTagName();
        $parent = end($ancestors);

        // Elements that aren't part of HTML are taken to be custom elements, which are transparent.
        $model = (true === ContentModels::contains($name)) ? ContentModels::get($name) : [ContentModels::TRANSPARENT];

        if (false !== $parent && Elements::NOSCRIPT === $name && Elements::HEAD === $parent->getTagName()) {
            return [Elements::LINK, Elements::STYLE, Elements::META];
        }

        if (false !== $parent && Elements::DIV === $name && Elements::DL === $parent->getTagName()) {
            return [Elements::DT, Elements::DD, Categories::SCRIPT_SUPPORTING];
        }

        if (false === in_array(ContentModels::TRANSPARENT, $model, true)) {
            return $model;
        }

        if (false === $parent) {
            return null;
        }

        $inherited = self::contentModel($parent, array_slice($ancestors, 0, -1));

        if (null === $inherited) {
            return null;
        }

        $own = array_diff($model, [ContentModels::TRANSPARENT]);

        return array_unique(array_merge($own, $inherited));
    }//end contentModel()

    /**
     * Checks whether a content model has room for text.
     *
     * @param string[] $model The content model.
     *
     * @return boolean
     */
    private static function allowsText(array $model): bool
    {
        return ([] !== array_intersect([ContentModels::TEXT, Categories::FLOW, Categories::PHRASING], $model));
    }//end allowsText()

    /**
     * Checks whether a content model has room for an element, by its name or by its categories.
     *
     * @param string[]         $model   The content model.
     * @param ElementInterface $element The element.
     *
     * @return boolean
     */
    private static function allowsElement(array $model, ElementInterface $element): bool
    {
        if (true === in_array($element->getTagName(), $model, true)) {
            return true;
        }

        return ([] !== array_intersect(Categorizer::categoriesOf($element), $model));
    }//end allowsElement()

    /**
     * Puts a content model into words.
     *
     * @param string[] $model The content model.
     *
     * @return string
     */
    private static function describe(array $model): string
    {
        if ([] === $model) {
            return 'Nothing is allowed there.';
        }

        $words = [];

        foreach ($model as $entry) {
            if (ContentModels::TEXT === $entry) {
                $words[] = 'text';
            } elseif (Categories::SCRIPT_SUPPORTING === $entry) {
                $words[] = 'script-supporting elements';
            } elseif (true === Categories::contains($entry)) {
                $words[] = $entry.' content';
            } else {
                $words[] = '<'.$entry.'>';
            }
        }

        return 'Allowed there: '.implode(', ', $words).'.';
    }//end describe()

    /**
     * Spells out where a node sits, as the tag names leading down to it.
     *
     * @param ElementInterface[] $ancestors The elements the node sits inside of, outermost first.
     * @param NodeInterface      $node      The node itself.
     *
     * @return string
     */
    private static function path(array $ancestors, NodeInterface $node): string
    {
        $names = array_map(fn (ElementInterface $ancestor) => $ancestor->getTagName(), $ancestors);

        if ($node instanceof ElementInterface) {
            $names[] = $node->getTagName();
        } elseif ($node instanceof Text) {
            $names[] = '(text)';
        } else {
            $names[] = '(doctype)';
        }

        return implode(' > ', $names);
    }//end path()
}//end class
