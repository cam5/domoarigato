<?php

namespace Cam5\Domoarigato\Attributes;

use Cam5\Domoarigato\Attributes\Traits as Traits;

/**
 * A simple, key-value pair attribute.
 */
class SimpleAttribute implements AttributeInterface
{
    use Traits\HasKey;

    /**
     * The value of the attribute.
     *
     * @var string
     */
    protected $value;

    /**
     * Get the value of a given attribute.
     *
     * Ex: "lorem" in <div id="lorem"></div>.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }//end getValue()

    /**
     * Set value for an attribute.
     *
     * @param string $string The value of the attribute.
     *
     * @return self
     */
    public function setValue(string $string)
    {
        $this->value = $string;

        return $this;
    }//end setValue()

    /**
     * Defines how the attribute is rendered into an HTML fragment.
     *
     * @return string
     */
    public function render()
    {
        return sprintf(
            '%s="%s"',
            $this->getKey(),
            $this->getValue()
        );
    }//end render()
}//end class
