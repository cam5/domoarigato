<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

interface AttributeInterface
{
    /**
     * Get the key of a given attribute.
     *
     * Ex: "id" in <div id="lorem"></div>.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Set key for an attribute.
     *
     * @param string $string The name of the key.
     *
     * @throws \InvalidArgumentException When the key is not a valid attribute name.
     *
     * @return static
     */
    public function setKey(string $string): static;

    /**
     * Set value for an attribute.
     *
     * Each kind of attribute decides which of these types make sense for it.
     *
     * @param string|integer|float|boolean|array|null $value The value of the attribute.
     *
     * @throws \InvalidArgumentException When the value does not suit the attribute.
     *
     * @return static
     */
    public function setValue(string|int|float|bool|array|null $value): static;

    /**
     * Whether the attribute has nothing to output, and should be left off the element.
     *
     * @return boolean
     */
    public function isEmpty(): bool;

    /**
     * A function that will output the attribute's HTML.
     *
     * @return string An empty string when the attribute `isEmpty()`.
     */
    public function render(): string;
}//end interface
