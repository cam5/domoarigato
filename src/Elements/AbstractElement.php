<?php

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Attributes\AttributeInterface;

/**
 * An abstract representation of an HTML element.
 *
 * Declares that anything extending it must have at minimum, a tag name.
 */
abstract class AbstractElement
{

    /**
     * An array of attributes.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    abstract public function getTagName();

    /**
     * Output the tag's formatted HTML.
     *
     * @return string
     */
    abstract public function render();

    /**
     * Renders attributes for the tag.
     *
     * Pads-left with a string when attrs are present, empty string if not.
     *
     * @return string
     */
    public function renderAttrs()
    {
        $html  = '';
        $attrs = $this->getAttributes();

        $renderedAttrs = array_map([$this, 'renderAttr'], $attrs);

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
    public function renderAttr(AttributeInterface $attribute)
    {
        return $attribute->render();
    }//end renderAttr()

    /**
     * Get the attributes attached to this element.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }//end getAttributes()

    /**
     * Adds an attribute to the element.
     *
     * @param string $key   The identifier of the attr.
     * @param string $value The value of of the attr.
     *
     * @return self
     */
    public function addAttribute(string $key, string $value)
    {
        $attr = new SimpleAttribute();

        $attr->setKey($key)
            ->setValue($value);

        $this->attributes[$key] = $attr;

        return $this;
    }//end addAttribute()
}//end class
