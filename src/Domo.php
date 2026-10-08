<?php

declare(strict_types=1);

namespace Cam5\Domoarigato;

use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Factories\ElementFactory;

class Domo
{
    public static function createElement(string $name): ElementInterface
    {
        return ElementFactory::createFromName($name);
    }//end createElement()
}
