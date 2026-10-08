<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Factories;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Enums\Attributes;

/**
 * Factory to create *Attribute objects.
 */
class AttributeFactory
{

    /**
     * Creates an attribute by name.
     *
     * Ex. 'id' will return a new `SimpleAttribute` object.
     * Ex. 'class' will return a new `CollectionAttribute` object.
     *
     * @param string $name The name of attribute key.
     *
     * @throws \InvalidArgumentException When the name is not a valid attribute name.
     *
     * @return AttributeInterface
     */
    public static function createFromName(string $name): AttributeInterface
    {
        $attr = new SimpleAttribute();

        if (true === Attributes::contains($name)) {
            $className = Attributes::get($name);

            $attr = new $className();
        }

        return $attr->setKey($name);
    }//end createFromName()
}//end class

