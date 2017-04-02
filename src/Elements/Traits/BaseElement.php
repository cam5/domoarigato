<?php

namespace Cam5\Domoarigato\Elements\Traits;

trait BaseElement
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName()
    {
        return self::TAG_NAME;
    }//end getTagName()
}//end trait
