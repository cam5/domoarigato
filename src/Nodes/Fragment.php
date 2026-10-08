<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * A list of sibling nodes without an element around them.
 */
class Fragment implements ParentNodeInterface
{
    use Traits\CastsToString;
    use Traits\HasChildren;

    /**
     * Constructor
     *
     * @param NodeInterface|string|integer|float ...$children The nodes to start out with.
     */
    public function __construct(NodeInterface|string|int|float ...$children)
    {
        $this->append(...$children);
    }//end __construct()

    /**
     * Gives a cloned fragment children of its own.
     *
     * @return void
     */
    public function __clone()
    {
        $this->cloneChildren();
    }//end __clone()

    /**
     * Output each of the nodes, one after the other.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getInnerHtml();
    }//end render()
}//end class
