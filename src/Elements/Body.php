<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <body> tag
 */
class Body extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'body';
}//end class
