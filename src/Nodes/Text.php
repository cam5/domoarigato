<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

use Cam5\Domoarigato\Support\Html;

/**
 * A run of text. Whatever it holds is shown to the reader as-is, never treated as markup.
 */
class Text implements NodeInterface
{
    use Traits\CastsToString;

    /**
     * The text, unescaped.
     *
     * @var string
     */
    protected string $text;

    /**
     * Constructor
     *
     * @param string $text The text, unescaped.
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }//end __construct()

    /**
     * Retrieve the text, unescaped.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return $this->text;
    }//end getTextContent()

    /**
     * Output the text, escaped for use in HTML.
     *
     * @param boolean $validate Makes no difference here: on its own, there is nothing about this node to check.
     *
     * @return string
     */
    public function render(bool $validate = false): string
    {
        return Html::escapeText($this->text);
    }//end render()
}//end class
