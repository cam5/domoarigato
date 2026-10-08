<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * Anything that can sit in a document: an element, some text, a comment...
 */
interface NodeInterface extends \Stringable
{
    /**
     * Output the node's formatted HTML.
     *
     * @param boolean $validate Whether to check the content against HTML's rules before rendering it.
     *
     * @throws \Cam5\Domoarigato\Validation\InvalidContentException When asked to validate, and the content isn't valid.
     *
     * @return string
     */
    public function render(bool $validate = false): string;

    /**
     * The text a reader would see in this node, with no markup and no escaping.
     *
     * @return string
     */
    public function getTextContent(): string;
}//end interface
