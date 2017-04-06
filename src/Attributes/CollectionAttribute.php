<?php

namespace Cam5\Domoarigato\Attributes;

use Cam5\Domoarigato\Attributes\Traits as Traits;

/**
 * A Collection Attribute has a value that resembles an array, more than a string.
 */
class CollectionAttribute implements AttributeInterface
{
    use Traits\HasKey;

    /**
     * Contains all the values that the attribute is holding.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Default value separator is a space, when rendered.
     *
     * @var string
     */
    protected $separator = ' ';

    /**
     * Retrieves the values of the attribute.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }//end getValues()

    /**
     * Passes single values onto main func.
     *
     * @param mixed $val The value intended to be set on the array.
     *
     * @return self
     */
    public function setValue($val)
    {
        $this->setValues($val);

        return $this;
    }//end setValue()

    /**
     * Sets many values at once, optionally overriding what came before.
     *
     * @param mixed   $vals   The values intended to be set on the array.
     * @param boolean $append Flag to add $vals to the end, or override all else.
     *
     * @return self
     */
    public function setValues($vals, $append = true)
    {
        if (false === $append) {
            $this->values = [];
        }

        if (true === is_array($vals)) {
            array_walk($vals, [$this, 'addValue']);
        } else {
            $this->addValue($vals);
        }

        return $this;
    }//end setValues()

    /**
     * Adds a value to the attribute.
     *
     * Idempotent.
     *
     * @param string $val The value to add.
     *
     * @return self
     */
    public function addValue($val)
    {
        if (false === array_search($val, $this->values)) {
            $this->values[] = (string) $val;
        }

        return $this;
    }//end addValue()

    /**
     * Takes a value away from the attribute.
     *
     * @param string $val The value to remove.
     *
     * @return self
     */
    public function removeValue($val)
    {
        $index = array_search($val, $this->values);

        if (false !== $index) {
            unset($this->values[$index]);
        }

        return $this;
    }//end removeValue()

    /**
     * Creates a 'view' for the attribute.
     *
     * @return string
     */
    public function render()
    {
        return sprintf(
            '%s="%s"',
            $this->getKey(),
            implode($this->separator, $this->getValues())
        );
    }//end render()
}//end class
