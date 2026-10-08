<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

use Cam5\Domoarigato\Attributes\Traits as Traits;

/**
 * A boolean attribute is "on" by being present, and "off" by being absent.
 *
 * Ex: "disabled" in <input disabled />.
 */
class BooleanAttribute implements AttributeInterface
{
    use Traits\HasKey;

    /**
     * Whether the attribute is present.
     *
     * @var boolean
     */
    protected bool $value = false;

    /**
     * Whether the attribute is present.
     *
     * @return boolean
     */
    public function getValue(): bool
    {
        return $this->value;
    }//end getValue()

    /**
     * Turn the attribute on or off.
     *
     * Alongside real booleans, the two spellings HTML allows for a boolean attribute's value are
     * understood as "on": the empty string, and the attribute's own name (disabled="disabled").
     * `null` is "off". Anything else is refused, because <input disabled="false" /> is disabled.
     *
     * @param string|integer|float|boolean|array|null $value Whether the attribute should be present.
     *
     * @throws \InvalidArgumentException When the value isn't one of the above.
     *
     * @return self
     */
    public function setValue(string|int|float|bool|array|null $value): static
    {
        if (null === $value) {
            $value = false;
        }

        if (true === is_string($value) && ('' === $value || 0 === strcasecmp($value, $this->getKey()))) {
            $value = true;
        }

        if (false === is_bool($value)) {
            throw new \InvalidArgumentException(
                'The boolean "'.$this->getKey().'" attribute only accepts true, false, null, "" or its own name.'
            );
        }

        $this->value = $value;

        return $this;
    }//end setValue()

    /**
     * Whether the attribute has nothing to output.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return (false === $this->value);
    }//end isEmpty()

    /**
     * Defines how the attribute is rendered into an HTML fragment.
     *
     * @return string
     */
    public function render(): string
    {
        if (true === $this->isEmpty()) {
            return '';
        }

        return $this->getKey();
    }//end render()
}//end class
