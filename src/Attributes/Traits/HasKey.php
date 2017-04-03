<?php

namespace Cam5\Domoarigato\Attributes\Traits;

trait HasKey
{

    /**
     * Attribute's key name.
     *
     * @var string
     */
    protected $key;

    /**
     * Get the key of a given attribute.
     *
     * Ex: "id" in <div id="lorem"></div>.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }//end getKey()

    /**
     * Set key for an attribute.
     *
     * @param string $string The name of the key.
     *
     * @return self
     */
    public function setKey($string)
    {
        $this->key = $string;

        return $this;
    }//end setKey()
}//end trait
