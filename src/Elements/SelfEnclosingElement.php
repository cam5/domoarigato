<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

/**
 * Extends the tag-only representation of an HTML element further by defining that it does not enclose text.
 */
abstract class SelfEnclosingElement extends AbstractElement
{

    /**
     * Flag to indicate if the tag is self-enclosed or not.
     *
     * @var boolean
     */
    protected bool $isSelfEnclosed = true;

    /**
     * Nothing can go inside a self-enclosing element, text included.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return '';
    }//end getTextContent()

    /**
     * Generates the HTML for the tag.
     *
     * @return string
     */
    public function render(): string
    {
        return sprintf(
            '<%1$s%2$s />',
            $this->getTagName(),
            $this->renderAttrs()
        );
    }//end render()
}//end class

