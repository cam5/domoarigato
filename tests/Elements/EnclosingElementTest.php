<?php

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Test elements descended from the abstract `EnclosingElement`
 *
 * @covers Cam5\Domoarigato\Elements\EnclosingElement
 */
final class EnclosingElementTest extends TestCase
{

    /**
     * An element object with methods we'll be testing.
     *
     * @var EnclosingElement
     */
    public $el;

    /**
     * Placeholder text to test with.
     *
     * @var string
     */
    public $text = 'lorem ipsum dolor sit amet';

    /**
     * PHPUnit convenience method run before each test* method.
     *
     * @return void
     */
    public function setUp()
    {
        // We know that 'div' is enclosing.
        $this->el = Domo::createElement('div');
    }//end setUp()

    /**
     * Test that text we set with `setTextContent` is retrievable using `getTextContent`.
     *
     * @return void
     */
    public function testGetAndSetText()
    {
        $this->el->setTextContent($this->text);

        $this->assertEquals(
            $this->el->getTextContent(),
            $this->text
        );
    }//end testGetAndSetText()

    /**
     * Render the text from the above test.
     *
     * @return void
     */
    public function testRendering()
    {
        $this->el->setTextContent($this->text);

        $this->assertEquals(
            $this->el->render(),
            '<div>'.$this->text.'</div>'
        );
    }//end testRendering()

    /**
     * Test attribute rendering.
     *
     * @covers Cam5\Domoarigato\Elements\AbstractElement::renderAttrs()
     * @covers Cam5\Domoarigato\Elements\AbstractElement::renderAttr()
     *
     * @return void
     */
    public function testAttributesRenderAsExpected()
    {
        $this->el->addAttribute('foo', 'bar')
            ->addAttribute('bar', 'baz');

        $this->assertEquals(
            ' foo="bar" bar="baz"',
            $this->el->renderAttrs()
        );

        $testAttr = new SimpleAttribute();
        $testAttr->setKey('lorem')
            ->setValue('ipsum');

        $this->assertEquals(
            $this->el->renderAttr($testAttr),
            'lorem="ipsum"'
        );
    }//end testAttributesRenderAsExpected()

    /**
     * Given an attribute, test that it's stored on the object.
     *
     * @covers Cam5\Domoarigato\Elements\AbstractElement::addAttribute()
     * @covers Cam5\Domoarigato\Elements\AbstractElement::getAttributes()
     *
     * @return void
     */
    public function testAttributesAreAdded()
    {
        $testAttr = new SimpleAttribute();
        $testAttr->setKey('lorem')
            ->setValue('ipsum');

        $this->assertCount(
            0,
            $this->el->getAttributes()
        );

        $this->el->addAttribute($testAttr->getKey(), $testAttr->getValue());

        $this->assertCount(
            1,
            $this->el->getAttributes()
        );

        $this->assertInstanceOf(
            SimpleAttribute::class,
            $this->el->getAttributes()['lorem']
        );
    }//end testAttributesAreAdded()
}//end class

