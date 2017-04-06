<?php

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Tests `CollectionAttribute`
 *
 * @covers Cam5\Domoarigato\Attributes\CollectionAttribute
 */
final class CollectionAttributeTest extends TestCase
{
    /**
     * Sets up an element between each test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->attr = new CollectionAttribute();
        $this->attr->setKey('foo');
    }//end setUp()


    /**
     * Tests the key getters and setters of CollectionAttribute
     *
     * @return void
     */
    public function testGetAndSetKeys()
    {
        $this->assertEquals(
            'foo',
            $this->attr->getKey()
        );
    }//end testGetAndSetKeys()

    /**
     * Can we "set" single values? Can we override values set earlier?
     *
     * @return void
     */
    public function testAddSingularValues()
    {
        $this->attr->setValue('bar');

        $this->assertEquals(
            'foo="bar"',
            $this->attr->render()
        );

        $this->attr->setValues('boo', false);

        $this->assertEquals(
            'foo="boo"',
            $this->attr->render()
        );
    }//end testAddSingularValues()

    /**
     * If we add a value twice, will we wind up with it in duplicate?
     *
     * @return void
     */
    public function testIdempotencyOfAddingValues()
    {
        $this->attr->addValue('ooga');

        $this->assertEquals(
            'foo="ooga"',
            $this->attr->render()
        );

        // Test idempotency of "add value".
        $this->attr->addValue('ooga');
        $this->attr->addValue('booga');

        $this->assertEquals(
            'foo="ooga booga"',
            $this->attr->render()
        );
    }//end testIdempotencyOfAddingValues()

    /**
     * Test that without second arg, values are appended.
     *
     * @return void
     */
    public function testAddMultipleValues()
    {
        $this->attr->setValues('boo');
        $this->attr->setValues('hoo');

        $this->assertEquals(
            'foo="boo hoo"',
            $this->attr->render()
        );

    }//end testAddMultipleValues()

    /**
     * Test that we can wipe out values set earlier if need be.
     *
     * @return void
     */
    public function testAddMultipleValuesWithoutAppending()
    {
        // Set many values at once.
        $this->attr->setKey('many')
            ->setValues(['this', 'is', 'not', 'permanent'])
            ->setValues(['multitude', 'myriad', 'mucho'], false);

        $this->assertEquals(
            'many="multitude myriad mucho"',
            $this->attr->render()
        );
    }//end testAddMultipleValuesWithoutAppending()

    /**
     * If we have a value to remove, can we get it out?
     *
     * @return void
     */
    public function testRemoveValues()
    {
        $this->attr->setKey('many')
            ->setValues(['multitude', 'myriad', 'mucho'], false);

        $this->attr->removeValue('myriad');

        $this->assertEquals(
            'many="multitude mucho"',
            $this->attr->render()
        );
    }//end testRemoveValues()
}//end class
