<?php
declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Div;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Elements\ElementInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Cam5\Domoarigato\Domo
 */
final class DomoTest extends TestCase
{
    public function testCreatesADivByName()
    {
        $this->assertInstanceOf(
            Div::class,
            Domo::createElement('div')
        );
    }

    public function testCreatesGenericElementByName()
    {
        $this->assertInstanceOf(
            GenericElement::class,
            Domo::createElement('test-element')
        );
    }

    public function testCreatesElementsConformingToACommonInterface()
    {
        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('any-old-thing')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('input')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('div')
        );
    }
}
