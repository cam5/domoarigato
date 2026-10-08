<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements\Traits;

trait BaseElement
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }//end getTagName()
}//end trait
