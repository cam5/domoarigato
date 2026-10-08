<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes;

use Cam5\Domoarigato\Attributes\Traits as Traits;
use Cam5\Domoarigato\Support\Html;

/**
 * A Collection Attribute has a value that resembles an array, more than a string.
 *
 * By default it is a set of space-separated tokens, like "class".
 */
class CollectionAttribute implements AttributeInterface
{
    use Traits\HasKey;

    /**
     * Contains all the values that the attribute is holding.
     *
     * @var string[]
     */
    protected array $values = [];

    /**
     * Default value separator is a space, when rendered.
     *
     * @var string
     */
    protected string $separator = ' ';

    /**
     * Retrieves the values of the attribute.
     *
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }//end getValues()

    /**
     * Passes single values onto main func.
     *
     * @param string|integer|float|boolean|array|null $val The value intended to be set on the array.
     *
     * @throws \InvalidArgumentException When given a boolean, or a number that is not finite.
     *
     * @return self
     */
    public function setValue(string|int|float|bool|array|null $val): static
    {
        $this->setValues($val);

        return $this;
    }//end setValue()

    /**
     * Sets many values at once, optionally overriding what came before.
     *
     * @param string|integer|float|boolean|array|null $vals   The values intended to be set on the array.
     * @param boolean                                 $append Flag to add $vals to the end, or override all else.
     *
     * @throws \InvalidArgumentException When given a boolean, or a number that is not finite.
     *
     * @return self
     */
    public function setValues(string|int|float|bool|array|null $vals, bool $append = true): static
    {
        if (false === $append) {
            $this->values = [];
        }

        if (true === is_array($vals)) {
            foreach ($vals as $val) {
                $this->addTokens($this->splitEntry($this->stringify($val)));
            }
        } elseif (null !== $vals) {
            $this->addTokens($this->split($this->stringify($vals)));
        }

        return $this;
    }//end setValues()

    /**
     * Adds a value to the attribute.
     *
     * Idempotent. A string holding several values ("foo bar") adds each one of them.
     *
     * @param string|integer|float $val The value to add.
     *
     * @throws \InvalidArgumentException When given a number that is not finite.
     *
     * @return self
     */
    public function addValue(string|int|float $val): static
    {
        return $this->addTokens($this->split($this->stringify($val)));
    }//end addValue()

    /**
     * Takes a value away from the attribute.
     *
     * A string holding several values ("foo bar") removes each one of them.
     *
     * @param string|integer|float $val The value to remove.
     *
     * @return self
     */
    public function removeValue(string|int|float $val): static
    {
        $this->values = array_values(array_diff($this->values, $this->split($this->stringify($val))));

        return $this;
    }//end removeValue()

    /**
     * Checks whether the attribute holds a value.
     *
     * A string holding several values ("foo bar") is only a match when all of them are present.
     *
     * @param string|integer|float $val The value to look for.
     *
     * @return boolean
     */
    public function hasValue(string|int|float $val): bool
    {
        $tokens = $this->split($this->stringify($val));

        return ([] !== $tokens && [] === array_diff($tokens, $this->values));
    }//end hasValue()

    /**
     * Whether the attribute has nothing to output.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return ([] === $this->values);
    }//end isEmpty()

    /**
     * Creates a 'view' for the attribute.
     *
     * @return string
     */
    public function render(): string
    {
        if (true === $this->isEmpty()) {
            return '';
        }

        return sprintf(
            '%s="%s"',
            $this->getKey(),
            Html::escapeAttribute(implode($this->separator, $this->getValues()))
        );
    }//end render()

    /**
     * Breaks a string into the individual values it holds.
     *
     * @param string $string One or more values, as they'd be written in HTML.
     *
     * @return string[]
     */
    protected function split(string $string): array
    {
        return preg_split('/[ \t\n\f\r]+/', $string, -1, PREG_SPLIT_NO_EMPTY);
    }//end split()

    /**
     * Breaks a single array entry into the individual values it holds.
     *
     * A space can never be part of a space-separated token, so entries get split as well.
     *
     * @param string $string An entry from an array of values.
     *
     * @return string[]
     */
    protected function splitEntry(string $string): array
    {
        return $this->split($string);
    }//end splitEntry()

    /**
     * Appends the tokens that aren't already present.
     *
     * @param string[] $tokens The individual values to add.
     *
     * @return self
     */
    protected function addTokens(array $tokens): static
    {
        foreach ($tokens as $token) {
            if (false === in_array($token, $this->values, true)) {
                $this->values[] = $token;
            }
        }

        return $this;
    }//end addTokens()

    /**
     * Turns anything we accept as a value into a string.
     *
     * @param mixed $val The value to convert.
     *
     * @throws \InvalidArgumentException When the value isn't a string or a finite number.
     *
     * @return string
     */
    protected function stringify(mixed $val): string
    {
        if (true === is_string($val) || true === is_int($val) || (true === is_float($val) && true === is_finite($val))) {
            return (string) $val;
        }

        throw new \InvalidArgumentException(
            'The "'.$this->getKey().'" attribute only holds strings and finite numbers.'
        );
    }//end stringify()
}//end class
