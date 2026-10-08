<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation\Traits;

use Cam5\Domoarigato\Validation\InvalidContentException;
use Cam5\Domoarigato\Validation\Validator;
use Cam5\Domoarigato\Validation\Violation;

trait Validates
{
    /**
     * Finds everything in this node, and inside of it, that breaks HTML's content rules.
     *
     * @return Violation[] Empty when everything is in order.
     */
    public function validate(): array
    {
        return Validator::validate($this);
    }//end validate()

    /**
     * Checks this node's content before it is rendered, when asked to.
     *
     * @param boolean $validate Whether to check at all.
     *
     * @throws InvalidContentException When asked to validate, and the content isn't valid.
     *
     * @return void
     */
    protected function validateWhen(bool $validate): void
    {
        if (true === $validate) {
            Validator::assertValid($this);
        }
    }//end validateWhen()
}//end trait
