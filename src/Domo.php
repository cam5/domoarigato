<?php

namespace Cam5\Domoarigato;

use Cam5\Domoarigato\Factories\ElementFactory;

class Domo
{
    public static function createElement($name)
    {
        return ElementFactory::createFromName($name);
    }//end createElement()
}
