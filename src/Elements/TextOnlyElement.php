<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\Text;

/**
 * An element whose content is text and nothing else, like <title> and <textarea>.
 *
 * A browser doesn't read tags inside of these, so there is no use in allowing them.
 */
abstract class TextOnlyElement extends EnclosingElement
{
    /**
     * Turns whatever we were handed into text, or refuses it.
     *
     * @param NodeInterface|string|integer|float $child The text.
     *
     * @throws \InvalidArgumentException When given a node that isn't text.
     *
     * @return NodeInterface
     */
    protected function toNode(NodeInterface|string|int|float $child): NodeInterface
    {
        if ($child instanceof NodeInterface && false === ($child instanceof Text)) {
            throw new \InvalidArgumentException('A "'.$this->getTagName().'" element can only contain text.');
        }

        return parent::toNode($child);
    }//end toNode()
}//end class
