<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\Factories;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Enums\Elements;

/**
 * Factory to create *Element objects.
 */
class ElementFactory
{

    /**
     * Creates an element by the name of the tag.
     *
     * Ex. 'div' will return a new `Div` object.
     *
     * @param string $name The tag name of the element.
     *
     * @return ElementInterface
     */
    public static function createFromName(string $name): ElementInterface
    {
        if (true === Elements::contains($name)) {
            $className = Elements::get($name);

            return new $className();
        }

        return new GenericElement($name);
    }//end createFromName()
}//end class
