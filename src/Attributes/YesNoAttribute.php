<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

/**
 * An attribute whose two states are spelled "yes" and "no".
 *
 * Ex: "translate" in <span translate="no"></span>.
 */
class YesNoAttribute extends TrueFalseAttribute
{

    /**
     * The keyword written for `true`.
     *
     * @var string
     */
    protected string $on = 'yes';

    /**
     * The keyword written for `false`.
     *
     * @var string
     */
    protected string $off = 'no';
}//end class
