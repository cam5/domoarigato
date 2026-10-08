<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;

/**
 * An element whose content is code rather than markup: <script> and <style>.
 *
 * A browser reads everything up to the closing tag literally, entities and all. So the content
 * is written exactly as given, and anything that would close the element early is refused.
 */
abstract class RawTextElement extends EnclosingElement
{
    /**
     * Adds to the code after what is already here.
     *
     * @param NodeInterface|string|integer|float $child The code to add.
     *
     * @throws \InvalidArgumentException When given something other than text, or text that can't be written safely.
     *
     * @return self
     */
    public function appendChild(NodeInterface|string|int|float $child): static
    {
        return $this->guard(fn () => parent::appendChild($child));
    }//end appendChild()

    /**
     * Adds to the code before what is already here.
     *
     * @param NodeInterface|string|integer|float $child The code to add.
     *
     * @throws \InvalidArgumentException When given something other than text, or text that can't be written safely.
     *
     * @return self
     */
    public function prependChild(NodeInterface|string|int|float $child): static
    {
        return $this->guard(fn () => parent::prependChild($child));
    }//end prependChild()

    /**
     * Adds any number of pieces of code after what is already here.
     *
     * @param NodeInterface|string|integer|float ...$children The code to add.
     *
     * @throws \InvalidArgumentException When given something other than text, or text that can't be written safely.
     *
     * @return self
     */
    public function append(NodeInterface|string|int|float ...$children): static
    {
        return $this->guard(fn () => parent::append(...$children));
    }//end append()

    /**
     * Replaces the code.
     *
     * @param string|integer|float $string The code. It is written as-is, not escaped.
     *
     * @throws \InvalidArgumentException When the code can't be written safely.
     *
     * @return self
     */
    public function setTextContent(string|int|float $string): static
    {
        return $this->guard(fn () => parent::setTextContent($string));
    }//end setTextContent()

    /**
     * Replaces the code. Here, this does just what `setTextContent` does.
     *
     * @param string $html The code. It is written as-is, not escaped.
     *
     * @throws \InvalidArgumentException When the code can't be written safely.
     *
     * @return self
     */
    public function setInnerHtml(string $html): static
    {
        return $this->guard(fn () => parent::setInnerHtml($html));
    }//end setInnerHtml()

    /**
     * Retrieve the code, exactly as it was given.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        $text = '';

        foreach ($this->children as $child) {
            $text .= ($child instanceof Text) ? $child->getTextContent() : $child->render();
        }

        return $text;
    }//end getTextContent()

    /**
     * Renders the code, exactly as it was given.
     *
     * @return string
     */
    public function getInnerHtml(): string
    {
        return $this->getTextContent();
    }//end getInnerHtml()

    /**
     * Explains what is wrong with a piece of code, when it can't go inside this element.
     *
     * @param string $content All of the code the element would hold.
     *
     * @return string|null Null when there is nothing wrong with it.
     */
    protected function findProblem(string $content): ?string
    {
        if (1 === preg_match('/<\/'.$this->getTagName().'(?:[\t\n\f\r \/>]|$)/i', $content)) {
            return 'contains "</'.$this->getTagName().'", which would end the element early';
        }

        return null;
    }//end findProblem()

    /**
     * Turns whatever we were handed into text, or refuses it.
     *
     * @param NodeInterface|string|integer|float $child The code.
     *
     * @throws \InvalidArgumentException When given a node that isn't text.
     *
     * @return NodeInterface
     */
    protected function toNode(NodeInterface|string|int|float $child): NodeInterface
    {
        if ($child instanceof NodeInterface
            && false === ($child instanceof Text)
            && false === ($child instanceof RawHtml)
        ) {
            throw new \InvalidArgumentException('A "'.$this->getTagName().'" element can only contain text.');
        }

        return parent::toNode($child);
    }//end toNode()

    /**
     * Makes a change, then undoes it if the code as a whole is no longer safe to write.
     *
     * The check has to look at everything together: "</scr" and "ipt>" are harmless on their own.
     *
     * @param callable $change The change to make.
     *
     * @throws \InvalidArgumentException When the result can't be written safely.
     *
     * @return self
     */
    private function guard(callable $change): static
    {
        $before = $this->children;

        $change();

        $problem = $this->findProblem($this->getTextContent());

        if (null !== $problem) {
            $this->children = $before;

            throw new \InvalidArgumentException(
                'That content cannot go inside of a "'.$this->getTagName().'" element: it '.$problem.'.'
            );
        }

        return $this;
    }//end guard()
}//end class
