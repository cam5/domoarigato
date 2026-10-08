<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

use Cam5\Domoarigato\Nodes\NodeInterface;

/**
 * One place where a tree of nodes breaks the rules of HTML.
 */
class Violation implements \Stringable
{

    /**
     * The node that is out of place.
     *
     * @var NodeInterface
     */
    protected NodeInterface $node;

    /**
     * Where the node sits, as the tag names leading down to it.
     *
     * @var string
     */
    protected string $path;

    /**
     * What is wrong.
     *
     * @var string
     */
    protected string $message;

    /**
     * Constructor
     *
     * @param NodeInterface $node    The node that is out of place.
     * @param string        $path    Where the node sits. Ex: "ul > li > p".
     * @param string        $message What is wrong.
     */
    public function __construct(NodeInterface $node, string $path, string $message)
    {
        $this->node    = $node;
        $this->path    = $path;
        $this->message = $message;
    }//end __construct()

    /**
     * Retrieves the node that is out of place.
     *
     * @return NodeInterface
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }//end getNode()

    /**
     * Retrieves where the node sits, as the tag names leading down to it.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }//end getPath()

    /**
     * Retrieves what is wrong.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }//end getMessage()

    /**
     * Describes the violation in a single line.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->path.': '.$this->message;
    }//end __toString()
}//end class
