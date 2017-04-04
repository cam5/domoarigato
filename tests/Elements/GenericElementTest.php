<?php

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\GenericElement;
use PHPUnit\Framework\TestCase;

/**
 * Test elements not explicitly supported by the library, eg. "GenericElement".
 *
 * @covers Cam5\Domoarigato\Elements\GenericElement
 */
final class GenericElementTest extends TestCase
{

    /**
     * An element object with methods we'll be testing.
     *
     * @var GenericElement
     */
    public $el;

    /**
     * PHPUnit convenience method run before each test* method.
     *
     * @return void
     */
    public function setUp()
    {
        $this->el = Domo::createElement('general');
    }//end setUp()

    /**
     * Test that the Generic Element can be instantiated in different ways.
     */
    public function testInstantiation()
    {
        // The factory produced the right class.
        $this->assertInstanceOf(
            GenericElement::class,
            $this->el
        );

        $newEl = new GenericElement('admiral');

        $this->assertInstanceOf(
            GenericElement::class,
            $newEl
        );

        $this->assertEquals(
            'admiral',
            $newEl->getTagName()
        );
    }//end testInstantiation()
}//end class

