<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <title> tag
 */
class Title extends TextOnlyElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'title';
}//end class
