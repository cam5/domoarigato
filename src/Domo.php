<?php

declare(strict_types=1);

namespace Cam5\Domoarigato;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Factories\ElementFactory;
use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Doctype;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;

class Domo
{
    /**
     * Creates an element, optionally with attributes and content already in place.
     *
     * @param string                                        $name       The tag name of the element.
     * @param array                                         $attributes Attribute values, keyed by name.
     * @param NodeInterface|string|integer|float|array|null $content    A node or some text to put inside,
     *                                                                  or an array of them.
     *
     * @throws \InvalidArgumentException When a name or value is invalid, or the element can't hold content.
     *
     * @return ElementInterface
     */
    public static function createElement(
        string $name,
        array $attributes = [],
        NodeInterface|string|int|float|array|null $content = null
    ): ElementInterface {
        $element = ElementFactory::createFromName($name);

        foreach ($attributes as $key => $value) {
            $element->addAttribute((string) $key, $value);
        }

        if (null === $content || [] === $content) {
            return $element;
        }

        if (false === ($element instanceof ParentNodeInterface)) {
            throw new \InvalidArgumentException(
                'A "'.$element->getTagName().'" element cannot have anything inside of it.'
            );
        }

        if (false === is_array($content)) {
            $content = [$content];
        }

        return $element->append(...array_values($content));
    }//end createElement()

    /**
     * Creates a run of text, which is escaped when rendered.
     *
     * @param string $text The text, unescaped.
     *
     * @return Text
     */
    public static function text(string $text): Text
    {
        return new Text($text);
    }//end text()

    /**
     * Wraps markup that is already written, so that it's output exactly as given.
     *
     * @param string $html Markup you trust.
     *
     * @return RawHtml
     */
    public static function raw(string $html): RawHtml
    {
        return new RawHtml($html);
    }//end raw()

    /**
     * Creates an HTML comment.
     *
     * @param string $text The text of the comment.
     *
     * @throws \InvalidArgumentException When the text can't be written inside of a comment.
     *
     * @return Comment
     */
    public static function comment(string $text): Comment
    {
        return new Comment($text);
    }//end comment()

    /**
     * Creates the HTML5 doctype.
     *
     * @return Doctype
     */
    public static function doctype(): Doctype
    {
        return new Doctype();
    }//end doctype()

    /**
     * Groups sibling nodes together, without an element around them.
     *
     * @param NodeInterface|string|integer|float ...$children The nodes, or text, to group.
     *
     * @return Fragment
     */
    public static function fragment(NodeInterface|string|int|float ...$children): Fragment
    {
        return new Fragment(...$children);
    }//end fragment()
}//end class
