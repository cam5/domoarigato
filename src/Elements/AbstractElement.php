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
     * Output the tag's formatted HTML.
     *
     * @return string
     */
    abstract public function render();

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
