<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Factories\AttributeFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests `AttributeFactory`
 */
#[CoversClass(AttributeFactory::class)]
final class AttributeFactoryTest extends TestCase
{
    /**
     * Tests that names in the enum get the class they're mapped to.
     *
     * @return void
     */
    public function testCreatesMappedAttributes(): void
    {
        $this->assertInstanceOf(CollectionAttribute::class, AttributeFactory::createFromName('class'));
        $this->assertInstanceOf(SimpleAttribute::class, AttributeFactory::createFromName('id'));
    }//end testCreatesMappedAttributes()

    /**
     * Tests that anything else falls back to a simple attribute.
     *
     * @return void
     */
    public function testFallsBackToSimpleAttributes(): void
    {
        $attr = AttributeFactory::createFromName('data-anything');

        $this->assertSame(SimpleAttribute::class, get_class($attr));
        $this->assertSame('data-anything', $attr->getKey());
    }//end testFallsBackToSimpleAttributes()

    /**
     * Tests that the mapping, and the stored key, ignore case.
     *
     * @return void
     */
    public function testIgnoresCase(): void
    {
        $attr = AttributeFactory::createFromName('CLASS');

        $this->assertInstanceOf(CollectionAttribute::class, $attr);
        $this->assertSame('class', $attr->getKey());
    }//end testIgnoresCase()

    /**
     * Tests that each call gets an attribute of its own.
     *
     * @return void
     */
    public function testCreatesANewAttributeEachTime(): void
    {
        $this->assertNotSame(
            AttributeFactory::createFromName('class'),
            AttributeFactory::createFromName('class')
        );
    }//end testCreatesANewAttributeEachTime()

    /**
     * Tests that invalid names don't make it past the factory.
     *
     * @return void
     */
    public function testRefusesInvalidNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AttributeFactory::createFromName('not valid');
    }//end testRefusesInvalidNames()

    /**
     * Tests that an empty name doesn't make it past the factory either.
     *
     * @return void
     */
    public function testRefusesAnEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AttributeFactory::createFromName('');
    }//end testRefusesAnEmptyName()
}//end class
