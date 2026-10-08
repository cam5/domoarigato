<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Support\Html;

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
     *
     * @throws \InvalidArgumentException When the name could not be written as a tag.
     */
    public function __construct(string $tagName)
    {
        $this->tagName = Html::normalizeTagName($tagName);
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
