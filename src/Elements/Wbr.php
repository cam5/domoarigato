<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <wbr /> tag
 */
class Wbr extends SelfEnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'wbr';
}//end class
