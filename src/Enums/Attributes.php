<?php

namespace Cam5\Domoarigato\Enums;

use Cam5\Domoarigato\Attributes as Attr;

/**
 * Static Enum of attributes explicitly supported.
 */
class Attributes extends StaticEnum
{
    const ID         = 'id';
    const ATTR_CLASS = 'class';

    /**
     * Map of names to classnames.
     *
     * @var array
     */
    protected static $keys = [
        self::ID         => Attr\SimpleAttribute::class,
        self::ATTR_CLASS => Attr\CollectionAttribute::class,
    ];
}//end class
