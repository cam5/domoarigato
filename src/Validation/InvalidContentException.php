<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Validation;

/**
 * Thrown when a tree of nodes is asked to render with validation, and isn't valid HTML.
 */
class InvalidContentException extends \DomainException
{

    /**
     * Everything that was found to be wrong.
     *
     * @var Violation[]
     */
    protected array $violations;

    /**
     * Constructor
     *
     * @param Violation[] $violations Everything that was found to be wrong.
     */
    public function __construct(array $violations)
    {
        $this->violations = array_values($violations);

        $lines = array_map(fn (Violation $violation) => '  - '.$violation, $this->violations);

        parent::__construct("The content is not valid HTML:\n".implode("\n", $lines));
    }//end __construct()

    /**
     * Retrieves everything that was found to be wrong.
     *
     * @return Violation[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }//end getViolations()
}//end class
