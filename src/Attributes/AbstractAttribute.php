<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

/**
 * Abstract representation of an HTML element's attribute.
 */
abstract class AbstractAttribute
{
    /**
     * Get the key of a given attribute.
     *
     * Ex: "id" in <div id="lorem"></div>.
     *
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Set key for an attribute.
     *
     * @param string $string The name of the key.
     *
     * @return self
     */
    abstract public function setKey(string $string): static;

    /**
     * Get the value of a given attribute.
     *
     * Ex: "lorem" in <div id="lorem"></div>.
     *
     * @return string|null
     */
    abstract public function getValue(): ?string;

    /**
     * Set value for an attribute.
     *
     * @param string $string The value of the attribute.
     *
     * @return self
     */
    abstract public function setValue(string $string): static;

    /**
     * Defines how the attribute is rendered into an HTML fragment.
     *
     * @return string
     */
    abstract public function render(): string;
}//end class
