<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Nodes\Traits;

trait CastsToString
{
    /**
     * Lets a node be echoed, or dropped into a string, as its HTML.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }//end __toString()
}//end trait
