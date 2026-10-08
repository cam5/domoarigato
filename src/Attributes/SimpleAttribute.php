<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

use Cam5\Domoarigato\Attributes\Traits as Traits;
use Cam5\Domoarigato\Support\Html;

/**
 * A simple, key-value pair attribute.
 */
class SimpleAttribute implements AttributeInterface
{
    use Traits\HasKey;

    /**
     * The value of the attribute.
     *
     * `true` means "present, without a value"; `false` and `null` mean "absent".
     *
     * @var string|boolean|null
     */
    protected string|bool|null $value = null;

    /**
     * Get the value of a given attribute.
     *
     * Ex: "lorem" in <div id="lorem"></div>.
     *
     * @return string|boolean|null
     */
    public function getValue(): string|bool|null
    {
        return $this->value;
    }//end getValue()

    /**
     * Set value for an attribute.
     *
     * Numbers are stored as strings. `true` renders the bare key (<a download>), while
     * `false` and `null` keep the attribute from rendering at all.
     *
     * @param string|integer|float|boolean|array|null $value The value of the attribute.
     *
     * @throws \InvalidArgumentException When given an array, or a float that is not finite.
     *
     * @return self
     */
    public function setValue(string|int|float|bool|array|null $value): static
    {
        if (true === is_array($value)) {
            throw new \InvalidArgumentException('The "'.$this->getKey().'" attribute cannot hold an array.');
        }

        if (true === is_float($value) && false === is_finite($value)) {
            throw new \InvalidArgumentException('The "'.$this->getKey().'" attribute needs a finite number.');
        }

        if (true === is_int($value) || true === is_float($value)) {
            $value = (string) $value;
        }

        $this->value = $value;

        return $this;
    }//end setValue()

    /**
     * Whether the attribute has nothing to output.
     *
     * An empty string is still a value: think <img alt="">.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return (null === $this->value || false === $this->value);
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

        if (true === $this->value) {
            return $this->getKey();
        }

        return sprintf(
            '%s="%s"',
            $this->getKey(),
            Html::escapeAttribute($this->value)
        );
    }//end render()
}//end class
