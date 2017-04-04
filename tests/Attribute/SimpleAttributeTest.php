<?php

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\SimpleAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Tests `SimpleAttribute`
 *
 * @covers Cam5\Domoarigato\Attributes\SimpleAttribute
 */
final class SimpleAttributeTest extends TestCase
{
    /**
     * Tests the getters and setters of SimpleAttribute
     *
     * @covers Cam5\Domoarigato\Attributes\SimpleAttribute::setKey()
     * @covers Cam5\Domoarigato\Attributes\SimpleAttribute::getKey()
     *
     * @return void
     */
    public function testGetAndSetKeys()
    {
        $attr = new SimpleAttribute();

        $attr->setKey('foo');

        $this->assertEquals(
            'foo',
            $attr->getKey()
        );
    }//end testGetAndSetKeys()

    /**
     * Tests the isolated rendering of the SimpleAttribute.
     *
     * @return void
     */
    public function testAttributeRender()
    {
        $attr = new SimpleAttribute();

        $attr->setKey('foo')
            ->setValue('bar');

        $this->assertEquals(
            'foo="bar"',
            $attr->render()
        );
    }//end testAttributeRender()
}//end class
