<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <style> tag
 */
class Style extends RawTextElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'style';
}//end class
