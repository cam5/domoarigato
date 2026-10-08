<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements\Traits;

trait KeepsLeadingNewline
{
    /**
     * Generates the HTML that goes between the element's tags.
     *
     * A browser throws away a newline that comes straight after the opening tag of a <pre> or a
     * <textarea>. Content that really does begin with one gets a second, to take the fall.
     *
     * @return string
     */
    protected function renderContent(): string
    {
        $html = $this->getInnerHtml();

        if (true === str_starts_with($html, "\n") || true === str_starts_with($html, "\r")) {
            return "\n".$html;
        }

        return $html;
    }//end renderContent()
}//end trait
