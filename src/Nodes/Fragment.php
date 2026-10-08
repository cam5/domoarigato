<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

use Cam5\Domoarigato\Validation\Traits\Validates;

/**
 * A list of sibling nodes without an element around them.
 */
class Fragment implements ParentNodeInterface
{
    use Traits\CastsToString;
    use Traits\HasChildren;
    use Validates;

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
     *
     * @param boolean $validate Whether to check the content against HTML's rules before rendering it.
     *
     * @throws \Cam5\Domoarigato\Validation\InvalidContentException When asked to validate, and the content isn't valid.
     *
     * @return string
     */
    public function render(bool $validate = false): string
    {
        $this->validateWhen($validate);

        return $this->getInnerHtml();
    }//end render()
}//end class
