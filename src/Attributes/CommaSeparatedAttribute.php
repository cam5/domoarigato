<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

/**
 * A Collection Attribute whose values are separated by commas, like "accept" or "srcset".
 */
class CommaSeparatedAttribute extends CollectionAttribute
{

    /**
     * Values are rendered with a comma and a space between them.
     *
     * @var string
     */
    protected string $separator = ', ';

    /**
     * Breaks a string into the individual values it holds.
     *
     * @param string $string One or more values, as they'd be written in HTML.
     *
     * @return string[]
     */
    protected function split(string $string): array
    {
        $values = [];

        foreach (explode(',', $string) as $value) {
            $values = array_merge($values, $this->splitEntry($value));
        }

        return $values;
    }//end split()

    /**
     * Takes a single array entry as one value, whatever it contains.
     *
     * This is the way to add a value that has a comma of its own, such as a URL in "srcset".
     *
     * @param string $string An entry from an array of values.
     *
     * @return string[]
     */
    protected function splitEntry(string $string): array
    {
        $string = trim($string, " \t\n\f\r");

        if ('' === $string) {
            return [];
        }

        return [$string];
    }//end splitEntry()
}//end class
