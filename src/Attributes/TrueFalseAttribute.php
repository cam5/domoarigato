<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

/**
 * An attribute that looks boolean, but has to spell its state out.
 *
 * Ex: "draggable" in <div draggable="false"></div>. Leaving it off would mean "auto", not "no".
 */
class TrueFalseAttribute extends SimpleAttribute
{

    /**
     * The keyword written for `true`.
     *
     * @var string
     */
    protected string $on = 'true';

    /**
     * The keyword written for `false`.
     *
     * @var string
     */
    protected string $off = 'false';

    /**
     * Set value for an attribute, turning booleans into their keywords.
     *
     * Strings pass through untouched, for the attributes with more states than two
     * (contenteditable="plaintext-only"). `null` still keeps the attribute from rendering.
     *
     * @param string|integer|float|boolean|array|null $value The value of the attribute.
     *
     * @throws \InvalidArgumentException When given an array, or a float that is not finite.
     *
     * @return self
     */
    public function setValue(string|int|float|bool|array|null $value): static
    {
        if (true === is_bool($value)) {
            $value = (true === $value) ? $this->on : $this->off;
        }

        return parent::setValue($value);
    }//end setValue()
}//end class
