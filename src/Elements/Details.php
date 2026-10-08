<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <details> tag
 */
class Details extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'details';
}//end class
