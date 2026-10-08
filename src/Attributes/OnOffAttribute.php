<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

/**
 * An attribute whose two states are spelled "on" and "off".
 *
 * Ex: "autocorrect" in <input autocorrect="off" />.
 */
class OnOffAttribute extends TrueFalseAttribute
{

    /**
     * The keyword written for `true`.
     *
     * @var string
     */
    protected string $on = 'on';

    /**
     * The keyword written for `false`.
     *
     * @var string
     */
    protected string $off = 'off';
}//end class
