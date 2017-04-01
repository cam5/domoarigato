<?php

namespace Cam5\Domoarigato\Elements\Traits;

trait BaseElement
{

    /**
     * A collection of element attributes.
     *
     * @var AttributeCollection
     */
    protected $attrs;

    /**
     * Gets the name of the HTML tag.
     *
     * @return string
     */
    public function getTagName()
    {
        return self::TAG_NAME;
    }//end getTagName()

    /**
     * Gets the elements 'attributes'.
     *
     * @return obj
     */
    public function getAttributes()
    {
        // @TODO get all attrs from attr cls.
        return $this->attrs;
    }//end getAttributes()

    /**
     * Sets the elements 'attributes'.
     *
     * @return self
     */
    public function setAttributes()
    {
        // @TODO set attrs, or operate on attr cls.
        return $this;
    }//end setAttributes()
}//end trait
