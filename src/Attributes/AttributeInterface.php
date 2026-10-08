<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

interface AttributeInterface
{
    /**
     * A function that will output the element's HTML.
     *
     * @return string
     */
    public function render(): string;
}//end interface
