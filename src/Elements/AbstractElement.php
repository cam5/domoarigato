<?php

namespace Cam5\Domoarigato\Elements;

/**
 * An abstract representation of an HTML element.
 *
 * Declares that anything extending it must have at minimum, a tag name.
 */
abstract class AbstractElement
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    abstract public function getTagName();

    /**
     * Generates the HTML for the tag.
     *
     * @return string
     */
    public function render()
    {
        if (true === $this->isSelfEnclosed) {
            return sprintf(
                '<%1$s%2$s />',
                $this->getTagName(),
                $this->renderAttrs()
            );
        }

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $this->getTagName(),
            $this->renderAttrs(),
            $this->getTextContent()
        );

    }//end render()

    /**
     * Renders attributes for the tag.
     *
     * @return string
     */
    public function renderAttrs()
    {
        return '';
    }//end renderAttrs()
}//end class
