<?php

namespace Cam5\Domoarigato\Elements;

/**
 * Extends the tag-only representation of an HTML element further by defining that it does not enclose text.
 */
abstract class SelfEnclosingElement extends AbstractElement
{

    /**
     * Flag to indicate if the tag is self-enclosed or not.
     *
     * @var boolean
     */
    protected $isSelfEnclosed = true;
}//end class

