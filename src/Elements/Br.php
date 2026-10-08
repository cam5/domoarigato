<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <br /> tag
 */
class Br extends SelfEnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'br';
}//end class
