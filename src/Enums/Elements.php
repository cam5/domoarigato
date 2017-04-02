<?php

namespace Cam5\Domoarigato\Enums;

use Cam5\Domoarigato\Elements as El;

/**
 * Static Enum of elements explicitly supported.
 */
class Elements extends StaticEnum
{
    const DIV   = 'div';
    const INPUT = 'input';

    /**
     * Map of names to classnames.
     *
     * @var array
     */
    protected static $keys = [
        self::DIV   => El\Div::class,
        self::INPUT => El\Input::class,
    ];
}//end class
