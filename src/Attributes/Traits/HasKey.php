<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Attributes\Traits;

use Cam5\Domoarigato\Support\Html;

trait HasKey
{

    /**
     * Attribute's key name.
     *
     * @var string
     */
    protected string $key = '';

    /**
     * Get the key of a given attribute.
     *
     * Ex: "id" in <div id="lorem"></div>.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }//end getKey()

    /**
     * Set key for an attribute.
     *
     * Keys are case-insensitive in HTML, so they are stored lowercased.
     *
     * @param string $string The name of the key.
     *
     * @throws \InvalidArgumentException When the key is not a valid attribute name.
     *
     * @return self
     */
    public function setKey(string $string): static
    {
        $this->key = Html::normalizeAttributeName($string);

        return $this;
    }//end setKey()
}//end trait
