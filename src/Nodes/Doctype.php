<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * The HTML5 doctype, which belongs at the very top of a document.
 */
class Doctype implements NodeInterface
{
    use Traits\CastsToString;

    /**
     * A doctype isn't shown to the reader, so it has no text content.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return '';
    }//end getTextContent()

    /**
     * Output the doctype.
     *
     * @return string
     */
    public function render(): string
    {
        return '<!DOCTYPE html>';
    }//end render()
}//end class
