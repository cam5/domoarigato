<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

interface ElementInterface
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName(): string;

    /**
     * Output the tag's formatted HTML.
     *
     * @return string
     */
    public function render(): string;
}//end interface
