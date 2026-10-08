<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Elements\Traits as Traits;

/**
 * Represents the HTML <script> tag
 */
class Script extends RawTextElement implements ElementInterface
{
    use Traits\BaseElement;

    const TAG_NAME = 'script';

    /**
     * Explains what is wrong with a piece of code, when it can't go inside this element.
     *
     * Scripts have a trap of their own. After a "<!--", a browser takes "<script" to open a
     * script within the script, and gives the next "</script>" to that one, leaving the real
     * element open for the rest of the page. Write them as "<\!--" and "<\script" in strings.
     *
     * @param string $content All of the code the element would hold.
     *
     * @return string|null Null when there is nothing wrong with it.
     */
    protected function findProblem(string $content): ?string
    {
        if (true === str_contains($content, '<!--') && 1 === preg_match('/<script(?:[\t\n\f\r \/>]|$)/i', $content)) {
            return 'contains both "<!--" and "<script", which would keep the element from ending';
        }

        return parent::findProblem($content);
    }//end findProblem()
}//end class
