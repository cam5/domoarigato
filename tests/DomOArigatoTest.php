<?php
declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\DomOArigato;
use PHPUnit\Framework\TestCase;

/**
 * @covers Cam5\Domoarigato\DomOArigato
 */
final class DomOArigatoTest extends TestCase
{
    public function testGreetsMe()
    {
        $this->assertEquals(
            'Hello World',
            DomOArigato::helloWorld()
        );
    }
}
