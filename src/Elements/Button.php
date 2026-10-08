<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <button> tag
 */
class Button extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'button';
}//end class
