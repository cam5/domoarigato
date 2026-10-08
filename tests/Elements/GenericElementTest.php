<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\GenericElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test elements not explicitly supported by the library, eg. "GenericElement".
 */
#[CoversClass(\Cam5\Domoarigato\Elements\GenericElement::class)]
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
    protected function setUp(): void
    {
        $this->el = Domo::createElement('general');
    }//end setUp()

    /**
     * Test that the Generic Element can be instantiated in different ways.
     */
    public function testInstantiation(): void
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

    /**
     * Test that tag names are lowercased, as HTML would read them anyway.
     *
     * @return void
     */
    public function testTagNamesAreLowercased(): void
    {
        $el = new GenericElement('My-Widget');

        $this->assertSame('my-widget', $el->getTagName());
        $this->assertSame('<my-widget></my-widget>', $el->render());
        $this->assertSame('<section></section>', Domo::createElement('SECTION')->render());
    }//end testTagNamesAreLowercased()

    /**
     * Test that a tag name can't be used to write arbitrary markup.
     *
     * @return void
     */
    public function testRefusesUnsafeTagNames(): void
    {
        foreach (['', 'a b', 'div onload=x', 'x><script>alert(1)</script', '1st', "p\n"] as $name) {
            try {
                new GenericElement($name);
                $this->fail('An invalid tag name was accepted.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('is not a valid tag name.', $e->getMessage());
            }
        }
    }//end testRefusesUnsafeTagNames()

    /**
     * Test that the factory refuses them too.
     *
     * @return void
     */
    public function testFactoryRefusesUnsafeTagNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('div class="x"');
    }//end testFactoryRefusesUnsafeTagNames()

    /**
     * Test that generic elements hold content and attributes like any other.
     *
     * @return void
     */
    public function testHoldsContentAndAttributes(): void
    {
        $this->el->setId('a')->setText('1 < 2');

        $this->assertSame('<general id="a">1 &lt; 2</general>', $this->el->render());
    }//end testHoldsContentAndAttributes()
}//end class
