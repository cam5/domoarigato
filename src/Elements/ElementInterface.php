<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Nodes\NodeInterface;

interface ElementInterface extends NodeInterface
{
    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName(): string;

    /**
     * Adds an attribute to the element, replacing any attribute already there by that name.
     *
     * @param string                                  $key   The identifier of the attr.
     * @param string|integer|float|boolean|array|null $value The value of of the attr.
     *
     * @throws \InvalidArgumentException When the key isn't a valid attribute name, or the value doesn't suit it.
     *
     * @return static
     */
    public function addAttribute(string $key, string|int|float|bool|array|null $value): static;

    /**
     * Get the attributes attached to this element.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array;

    /**
     * Retrieves an attribute object by name.
     *
     * @param string $key The identifier of the attr.
     *
     * @return AttributeInterface|null Null when the element has no such attribute.
     */
    public function getAttribute(string $key): ?AttributeInterface;

    /**
     * Checks whether the element will render an attribute by this name.
     *
     * @param string $key The identifier of the attr.
     *
     * @return boolean
     */
    public function hasAttribute(string $key): bool;

    /**
     * Takes an attribute off the element.
     *
     * @param string $key The identifier of the attr.
     *
     * @return static
     */
    public function removeAttribute(string $key): static;

    /**
     * Finds everything in this element, and inside of it, that breaks HTML's content rules.
     *
     * @return \Cam5\Domoarigato\Validation\Violation[] Empty when everything is in order.
     */
    public function validate(): array;

    /**
     * Output the tag's formatted HTML.
     *
     * @param boolean $validate Whether to check the content against HTML's rules before rendering it.
     *
     * @throws \Cam5\Domoarigato\Validation\InvalidContentException When asked to validate, and the content isn't valid.
     *
     * @return string
     */
    public function render(bool $validate = false): string;
}//end interface
