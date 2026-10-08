<?php

declare(strict_types=1);

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
    protected bool $isSelfEnclosed = false;

    /**
     * The text content of the element.
     *
     * @var string|null
     */
    protected ?string $textContent = null;

    /**
     * Retrieve the text content of the element.
     *
     * @return string|null
     */
    public function getTextContent(): ?string
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
    public function setTextContent(string $string): static
    {
        $this->textContent = $string;

        return $this;
    }//end setTextContent()

    /**
     * Generates the HTML for the tag.
     *
     * @return string
     */
    public function render(): string
    {
        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $this->getTagName(),
            $this->renderAttrs(),
            $this->getTextContent()
        );
    }//end render()
}//end class
