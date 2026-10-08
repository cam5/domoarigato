<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <h2> tag
 */
class H2 extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'h2';
}//end class
