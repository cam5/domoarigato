<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <pre> tag
 */
class Pre extends EnclosingElement implements ElementInterface
{
    use Traits\BaseElement;
    use Traits\KeepsLeadingNewline;

    const TAG_NAME = 'pre';
}//end class
