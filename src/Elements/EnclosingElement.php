<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Elements;

use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\Traits\HasChildren;

/**
 * Extends the tag-only representation of an HTML element further by defining that it encloses content.
 */
abstract class EnclosingElement extends AbstractElement implements ParentNodeInterface
{
    use HasChildren;

    /**
     * Flag to indicate if the tag is self-enclosed or not.
     *
     * @var boolean
     */
    protected bool $isSelfEnclosed = false;

    /**
     * Gives a cloned element attributes and children of its own.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();

        $this->cloneChildren();
    }//end __clone()

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
            $this->renderContent()
        );
    }//end render()

    /**
     * Generates the HTML that goes between the element's tags.
     *
     * @return string
     */
    protected function renderContent(): string
    {
        return $this->getInnerHtml();
    }//end renderContent()
}//end class
