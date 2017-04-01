<?php

namespace Cam5\Domoarigato\Elements;

/**
 * Extends the tag-only representation of an HTML element further by defining that it encloses text.
 */
abstract class EnclosingElement extends AbstractElement
{

    /**
     * Flag to indicate if the tag is self-enclosed or not.
     *
     * @var boolean
     */
    protected $isSelfEnclosed = false;

    /**
     * The text content of the element.
     *
     * @var string
     */
    protected $textContent;

    /**
     * Retrieve the text content of the element.
     *
     * @return string
     */
    public function getTextContent()
    {
        return $this->textContent;
    }//end getTextContent()

    /**
     * Set the content of the element.
     *
     * @param string $string The text content.
     *
     * @return self
     */
    public function setTextContent(string $string)
    {
        $this->textContent = $string;

        return $this;
    }//end setTextContent()
}//end class
