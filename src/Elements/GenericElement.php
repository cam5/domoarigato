<?php

declare(strict_types=1);

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
    protected string $tagName;

    /**
     * Constructor
     *
     * @param string $tagName The name of the element.
     */
    public function __construct(string $tagName)
    {
        $this->tagName = $tagName;
    }//end __construct()

    /**
     * Gets the name of the element.
     *
     * @return string
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }//end getTagName()
}//end class
