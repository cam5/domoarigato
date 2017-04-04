<?php

namespace Cam5\Domoarigato\Elements;

/**
 * A generic HTML element.
 */
class GenericElement extends EnclosingElement implements ElementInterface
{

    /**
     * The name of the element.
     *
     * @var string
     */
    protected $tagName;

    /**
     * Constructor
     *
     * @param string $tagName The name of the element.
     *
     * @return self
     */
    public function __construct($tagName)
    {
        $this->tagName = $tagName;

        return $this;
    }//end __construct()

    /**
     * Gets the name of the element.
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }//end getTagName()
}//end class
