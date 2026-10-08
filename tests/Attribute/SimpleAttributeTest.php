<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\SimpleAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests `SimpleAttribute`
 */
#[CoversClass(\Cam5\Domoarigato\Attributes\SimpleAttribute::class)]
final class SimpleAttributeTest extends TestCase
{
    /**
     * Tests the getters and setters of SimpleAttribute
     *
     * @return void
     */
    public function testGetAndSetKeys(): void
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
    public function testAttributeRender(): void
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
