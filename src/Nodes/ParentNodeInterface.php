<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes;

/**
 * A node that other nodes can be put inside of.
 */
interface ParentNodeInterface extends NodeInterface
{
    /**
     * Retrieves the nodes directly inside this one, in order.
     *
     * @return NodeInterface[]
     */
    public function getChildren(): array;

    /**
     * Checks whether a node is this one, or sits anywhere inside of it.
     *
     * @param NodeInterface $node The node to look for.
     *
     * @return boolean
     */
    public function contains(NodeInterface $node): bool;
}//end interface
