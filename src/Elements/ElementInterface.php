<?php

namespace Cam5\Domoarigato\Elements;

interface ElementInterface
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName();

    /**
     * Output the tag's formatted HTML.
     *
     * @return string
     */
    public function render();
}//end interface
