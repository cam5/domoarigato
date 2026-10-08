<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Elements\Div;
use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Enums\Attributes;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Enums\StaticEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the enums that map HTML names onto classes.
 */
#[CoversClass(StaticEnum::class)]
#[CoversClass(Attributes::class)]
#[CoversClass(Elements::class)]
final class StaticEnumTest extends TestCase
{
    /**
     * Tests looking up keys that are there.
     *
     * @return void
     */
    public function testGetsKnownKeys(): void
    {
        $this->assertSame(SimpleAttribute::class, Attributes::get('id'));
        $this->assertSame(CollectionAttribute::class, Attributes::get('class'));
        $this->assertSame(Div::class, Elements::get('div'));
    }//end testGetsKnownKeys()

    /**
     * Tests that lookups ignore case, as HTML does.
     *
     * @return void
     */
    public function testLookupsAreCaseInsensitive(): void
    {
        $this->assertTrue(Attributes::contains('CLASS'));
        $this->assertTrue(Elements::contains('Div'));
        $this->assertSame(CollectionAttribute::class, Attributes::get('Class'));
        $this->assertSame(Div::class, Elements::get('DIV'));
    }//end testLookupsAreCaseInsensitive()

    /**
     * Tests that each enum only knows its own keys.
     *
     * @return void
     */
    public function testEnumsDoNotShareKeys(): void
    {
        $this->assertFalse(Attributes::contains('div'));
        $this->assertFalse(Elements::contains('class'));
        $this->assertFalse(StaticEnum::contains('div'));
        $this->assertSame([], StaticEnum::all());
    }//end testEnumsDoNotShareKeys()

    /**
     * Tests the keys that aren't there.
     *
     * @return void
     */
    public function testDoesNotContainUnknownKeys(): void
    {
        $this->assertFalse(Attributes::contains('nope'));
        $this->assertFalse(Attributes::contains(''));
        $this->assertFalse(Attributes::contains(' class'));
        $this->assertFalse(Elements::contains('divv'));
    }//end testDoesNotContainUnknownKeys()

    /**
     * Tests that asking for an unknown key is an error that names the enum asked.
     *
     * @return void
     */
    public function testGettingAnUnknownKeyThrows(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Could not find "nope" key in '.Attributes::class.' enum.');

        Attributes::get('nope');
    }//end testGettingAnUnknownKeyThrows()

    /**
     * Tests that every entry is a lowercase name pointing at a class of the right kind.
     *
     * @return void
     */
    public function testEveryEntryPointsAtARealClass(): void
    {
        $this->assertNotEmpty(Attributes::all());
        $this->assertNotEmpty(Elements::all());

        foreach (Attributes::all() as $name => $class) {
            $this->assertSame(strtolower($name), $name);
            $this->assertTrue(is_subclass_of($class, AttributeInterface::class), $name);
        }

        foreach (Elements::all() as $name => $class) {
            $this->assertSame(strtolower($name), $name);
            $this->assertTrue(is_subclass_of($class, ElementInterface::class), $name);
        }
    }//end testEveryEntryPointsAtARealClass()
}//end class
