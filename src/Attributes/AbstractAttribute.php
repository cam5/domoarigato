<?php

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
    abstract public function getKey();

    /**
     * Set key for an attribute.
     *
     * @param string $string The name of the key.
     *
     * @return self
     */
    abstract public function setKey(string $string);

    /**
     * Get the value of a given attribute.
     *
     * Ex: "lorem" in <div id="lorem"></div>.
     *
     * @return string
     */
    abstract public function getValue();

    /**
     * Set value for an attribute.
     *
     * @param string $string The value of the attribute.
     *
     * @return self
     */
    abstract public function setValue(string $string);

    /**
     * Defines how the attribute is rendered into an HTML fragment.
     *
     * @return string
     */
    abstract public function render();
}//end class
