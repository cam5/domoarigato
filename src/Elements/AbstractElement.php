<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Enums\Attributes;
use Cam5\Domoarigato\Factories\AttributeFactory;
use Cam5\Domoarigato\Nodes\Traits\CastsToString;
use Cam5\Domoarigato\Validation\Traits\Validates;

/**
 * An abstract representation of an HTML element.
 *
 * Declares that anything extending it must have at minimum, a tag name.
 */
abstract class AbstractElement
{
    use CastsToString;
    use Validates;

    /**
     * An array of attributes.
     *
     * @var AttributeInterface[]
     */
    public array $attributes = [];

    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    abstract public function getTagName(): string;

    /**
     * Output the tag's formatted HTML.
     *
     * @param boolean $validate Whether to check the content against HTML's rules before rendering it.
     *
     * @throws \Cam5\Domoarigato\Validation\InvalidContentException When asked to validate, and the content isn't valid.
     *
     * @return string
     */
    abstract public function render(bool $validate = false): string;

    /**
     * Gives a cloned element attributes of its own, so changing one copy leaves the other alone.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->attributes as $key => $attribute) {
            $this->attributes[$key] = clone $attribute;
        }
    }//end __clone()

    /**
     * Renders attributes for the tag.
     *
     * Pads-left with a string when attrs are present, empty string if not.
     *
     * @return string
     */
    public function renderAttrs(): string
    {
        $html  = '';
        $attrs = $this->getAttributes();

        // Attributes that are switched off (a `false` boolean, an empty class list) render as nothing.
        $renderedAttrs = array_filter(array_map([$this, 'renderAttr'], $attrs), 'strlen');

        if (!empty($renderedAttrs)) {
            $html = ' '.implode(' ', $renderedAttrs);
        }

        return $html;
    }//end renderAttrs()

    /**
     * Renders an individual attribute
     *
     * @param AttributeInterface $attribute A renderable attribute object.
     *
     * @return string
     */
    public function renderAttr(AttributeInterface $attribute): string
    {
        return $attribute->render();
    }//end renderAttr()

    /**
     * Get the attributes attached to this element.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }//end getAttributes()

    /**
     * Adds an attribute to the element, replacing any attribute already there by that name.
     *
     * @param string                                  $key   The identifier of the attr.
     * @param string|integer|float|boolean|array|null $value The value of of the attr.
     *
     * @throws \InvalidArgumentException When the key isn't a valid attribute name, or the value doesn't suit it.
     *
     * @return self
     */
    public function addAttribute(string $key, string|int|float|bool|array|null $value): static
    {
        $attr = AttributeFactory::createFromName($key);

        $attr->setValue($value);

        $this->attributes[$attr->getKey()] = $attr;

        return $this;
    }//end addAttribute()

    /**
     * Alias of `addAttribute`, for those who think in DOM methods.
     *
     * @param string                                  $key   The identifier of the attr.
     * @param string|integer|float|boolean|array|null $value The value of of the attr.
     *
     * @throws \InvalidArgumentException When the key isn't a valid attribute name, or the value doesn't suit it.
     *
     * @return self
     */
    public function setAttribute(string $key, string|int|float|bool|array|null $value): static
    {
        return $this->addAttribute($key, $value);
    }//end setAttribute()

    /**
     * Retrieves an attribute object by name.
     *
     * @param string $key The identifier of the attr.
     *
     * @return AttributeInterface|null Null when the element has no such attribute.
     */
    public function getAttribute(string $key): ?AttributeInterface
    {
        $key = strtolower($key);

        if (true === array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }//end getAttribute()

    /**
     * Checks whether the element will render an attribute by this name.
     *
     * @param string $key The identifier of the attr.
     *
     * @return boolean
     */
    public function hasAttribute(string $key): bool
    {
        $attr = $this->getAttribute($key);

        return (null !== $attr && false === $attr->isEmpty());
    }//end hasAttribute()

    /**
     * Takes an attribute off the element.
     *
     * @param string $key The identifier of the attr.
     *
     * @return self
     */
    public function removeAttribute(string $key): static
    {
        unset($this->attributes[strtolower($key)]);

        return $this;
    }//end removeAttribute()

    /**
     * Sets the element's "id" attribute.
     *
     * @param string $id The unique identifier.
     *
     * @return self
     */
    public function setId(string $id): static
    {
        return $this->addAttribute(Attributes::ID, $id);
    }//end setId()

    /**
     * Gets the element's "id" attribute.
     *
     * @return string|null Null when the element has no id.
     */
    public function getId(): ?string
    {
        $attr = $this->getAttribute(Attributes::ID);

        if (null === $attr || false === is_string($attr->getValue())) {
            return null;
        }

        return $attr->getValue();
    }//end getId()

    /**
     * Adds one or more class names to the element.
     *
     * @param string|array $class A class name, several separated by spaces, or an array of them.
     *
     * @return self
     */
    public function addClass(string|array $class): static
    {
        $this->classList()->setValues($class);

        return $this;
    }//end addClass()

    /**
     * Removes one or more class names from the element.
     *
     * @param string|array $class A class name, several separated by spaces, or an array of them.
     *
     * @return self
     */
    public function removeClass(string|array $class): static
    {
        foreach ((array) $class as $name) {
            $this->classList()->removeValue($name);
        }

        return $this;
    }//end removeClass()

    /**
     * Checks whether the element has a class name.
     *
     * @param string $class A class name. Given several separated by spaces, all of them must be there.
     *
     * @return boolean
     */
    public function hasClass(string $class): bool
    {
        return $this->classList()->hasValue($class);
    }//end hasClass()

    /**
     * Sets a custom data attribute.
     *
     * Ex: `setData('user-id', 5)` renders data-user-id="5".
     *
     * @param string                              $name  The name, without its "data-" prefix.
     * @param string|integer|float|boolean|null $value The value to store.
     *
     * @throws \InvalidArgumentException When the name isn't a valid attribute name.
     *
     * @return self
     */
    public function setData(string $name, string|int|float|bool|null $value): static
    {
        return $this->addAttribute('data-'.$name, $value);
    }//end setData()

    /**
     * Sets an ARIA attribute.
     *
     * Ex: `setAria('label', 'Close')` renders aria-label="Close".
     *
     * ARIA states are spelled out, so booleans render as "true" and "false".
     *
     * @param string                              $name  The name, without its "aria-" prefix.
     * @param string|integer|float|boolean|null $value The value to store.
     *
     * @throws \InvalidArgumentException When the name isn't a valid attribute name.
     *
     * @return self
     */
    public function setAria(string $name, string|int|float|bool|null $value): static
    {
        if (true === is_bool($value)) {
            $value = (true === $value) ? 'true' : 'false';
        }

        return $this->addAttribute('aria-'.$name, $value);
    }//end setAria()

    /**
     * Retrieves the element's "class" attribute, creating it when need be.
     *
     * @return CollectionAttribute
     */
    protected function classList(): CollectionAttribute
    {
        if (null === $this->getAttribute(Attributes::ATTR_CLASS)) {
            $this->addAttribute(Attributes::ATTR_CLASS, null);
        }

        return $this->getAttribute(Attributes::ATTR_CLASS);
    }//end classList()
}//end class
