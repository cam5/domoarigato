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
     * @param boolean $validate Makes no difference here: on its own, there is nothing about this node to check.
     *
     * @return string
     */
    public function render(bool $validate = false): string
    {
        return '<!DOCTYPE html>';
    }//end render()
}//end class
