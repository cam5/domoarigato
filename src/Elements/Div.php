<?php

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <div> tag
 */
class Div extends EnclosingElement
{
    use Traits\BaseElement;

    const TAG_NAME = 'div';
}//end class

