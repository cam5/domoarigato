<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes\Traits;

use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;

trait HasChildren
{

    /**
     * The nodes directly inside this one, in order.
     *
     * @var NodeInterface[]
     */
    protected array $children = [];

    /**
     * Retrieves the nodes directly inside this one, in order.
     *
     * @return NodeInterface[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }//end getChildren()

    /**
     * Checks whether there is anything inside this node.
     *
     * @return boolean
     */
    public function hasChildren(): bool
    {
        return ([] !== $this->children);
    }//end hasChildren()

    /**
     * Adds a node after the ones already here.
     *
     * Strings and numbers become text, and are escaped when rendered.
     *
     * @param NodeInterface|string|integer|float $child The node, or text, to add.
     *
     * @throws \InvalidArgumentException When the node is this one, or already has this one inside of it.
     *
     * @return self
     */
    public function appendChild(NodeInterface|string|int|float $child): static
    {
        $this->children[] = $this->toNode($child);

        return $this;
    }//end appendChild()

    /**
     * Adds a node before the ones already here.
     *
     * @param NodeInterface|string|integer|float $child The node, or text, to add.
     *
     * @throws \InvalidArgumentException When the node is this one, or already has this one inside of it.
     *
     * @return self
     */
    public function prependChild(NodeInterface|string|int|float $child): static
    {
        array_unshift($this->children, $this->toNode($child));

        return $this;
    }//end prependChild()

    /**
     * Adds any number of nodes after the ones already here.
     *
     * @param NodeInterface|string|integer|float ...$children The nodes, or text, to add.
     *
     * @throws \InvalidArgumentException When a node is this one, or already has this one inside of it.
     *
     * @return self
     */
    public function append(NodeInterface|string|int|float ...$children): static
    {
        // Convert them all first, so that one bad node doesn't leave half of the others behind.
        $nodes = array_map([$this, 'toNode'], array_values($children));

        $this->children = array_merge($this->children, $nodes);

        return $this;
    }//end append()

    /**
     * Takes out everything inside this node.
     *
     * @return self
     */
    public function removeChildren(): static
    {
        $this->children = [];

        return $this;
    }//end removeChildren()

    /**
     * Checks whether a node is this one, or sits anywhere inside of it.
     *
     * @param NodeInterface $node The node to look for.
     *
     * @return boolean
     */
    public function contains(NodeInterface $node): bool
    {
        if ($node === $this) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child === $node || ($child instanceof ParentNodeInterface && true === $child->contains($node))) {
                return true;
            }
        }

        return false;
    }//end contains()

    /**
     * Retrieve the text content of this node and everything inside of it.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        $text = '';

        foreach ($this->children as $child) {
            $text .= $child->getTextContent();
        }

        return $text;
    }//end getTextContent()

    /**
     * Replaces everything inside this node with a piece of text.
     *
     * @param string|integer|float $string The text content. It is escaped when rendered.
     *
     * @return self
     */
    public function setTextContent(string|int|float $string): static
    {
        $string = (string) $string;

        $this->children = [];

        if ('' !== $string) {
            $this->children[] = new Text($string);
        }

        return $this;
    }//end setTextContent()

    /**
     * Alias of `setTextContent`.
     *
     * @param string|integer|float $string The text content. It is escaped when rendered.
     *
     * @return self
     */
    public function setText(string|int|float $string): static
    {
        return $this->setTextContent($string);
    }//end setText()

    /**
     * Renders everything inside this node.
     *
     * @return string
     */
    public function getInnerHtml(): string
    {
        $html = '';

        foreach ($this->children as $child) {
            $html .= $child->render();
        }

        return $html;
    }//end getInnerHtml()

    /**
     * Replaces everything inside this node with markup that is already written.
     *
     * The markup is output exactly as given. Only use it for HTML you trust.
     *
     * @param string $html The markup.
     *
     * @return self
     */
    public function setInnerHtml(string $html): static
    {
        $this->children = [];

        if ('' !== $html) {
            $this->children[] = new RawHtml($html);
        }

        return $this;
    }//end setInnerHtml()

    /**
     * Gives every child its own copy when this node is cloned.
     *
     * @return void
     */
    protected function cloneChildren(): void
    {
        foreach ($this->children as $index => $child) {
            $this->children[$index] = clone $child;
        }
    }//end cloneChildren()

    /**
     * Turns whatever we were handed into a node that's safe to put inside this one.
     *
     * @param NodeInterface|string|integer|float $child The node, or text.
     *
     * @throws \InvalidArgumentException When the node is this one, or already has this one inside of it.
     *
     * @return NodeInterface
     */
    protected function toNode(NodeInterface|string|int|float $child): NodeInterface
    {
        if (false === ($child instanceof NodeInterface)) {
            return new Text((string) $child);
        }

        if ($child === $this || ($child instanceof ParentNodeInterface && true === $child->contains($this))) {
            throw new \InvalidArgumentException('A node cannot be placed inside of itself.');
        }

        return $child;
    }//end toNode()
}//end trait
