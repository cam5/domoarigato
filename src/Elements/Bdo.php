<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <bdo> tag
 */
class Bdo extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'bdo';
}//end class
