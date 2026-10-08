<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Attributes\BooleanAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests `BooleanAttribute`
 */
#[CoversClass(BooleanAttribute::class)]
final class BooleanAttributeTest extends TestCase
{
    /**
     * Creates a "disabled" attribute to test with.
     *
     * @return BooleanAttribute
     */
    private function attr(): BooleanAttribute
    {
        $attr = new BooleanAttribute();

        return $attr->setKey('disabled');
    }//end attr()

    /**
     * Tests that it honours the contract every attribute shares.
     *
     * @return void
     */
    public function testImplementsTheInterface(): void
    {
        $this->assertInstanceOf(AttributeInterface::class, $this->attr());
    }//end testImplementsTheInterface()

    /**
     * Tests that the attribute is off until someone turns it on.
     *
     * @return void
     */
    public function testIsOffByDefault(): void
    {
        $attr = $this->attr();

        $this->assertFalse($attr->getValue());
        $this->assertTrue($attr->isEmpty());
        $this->assertSame('', $attr->render());
    }//end testIsOffByDefault()

    /**
     * Values, and whether they turn the attribute on.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            'true'                  => [true, true],
            'false'                 => [false, false],
            'null'                  => [null, false],
            'empty string'          => ['', true],
            'its own name'          => ['disabled', true],
            'its own name, shouted' => ['DISABLED', true],
            'its own name, mixed'   => ['Disabled', true],
        ];
    }//end values()

    /**
     * Tests the values that switch the attribute on and off.
     *
     * @param mixed   $value    The value as given.
     * @param boolean $expected Whether the attribute should be present.
     *
     * @return void
     */
    #[DataProvider('values')]
    public function testSwitchesOnAndOff(mixed $value, bool $expected): void
    {
        $attr = $this->attr();

        $this->assertSame($attr, $attr->setValue($value));
        $this->assertSame($expected, $attr->getValue());
        $this->assertSame(!$expected, $attr->isEmpty());
        $this->assertSame((true === $expected) ? 'disabled' : '', $attr->render());
    }//end testSwitchesOnAndOff()

    /**
     * Tests that the attribute can be switched back off.
     *
     * @return void
     */
    public function testCanBeToggled(): void
    {
        $attr = $this->attr()->setValue(true);

        $this->assertSame('disabled', $attr->render());
        $this->assertSame('', $attr->setValue(false)->render());
        $this->assertSame('disabled', $attr->setValue('')->render());
        $this->assertSame('', $attr->setValue(null)->render());
    }//end testCanBeToggled()

    /**
     * Values that look like they mean something, but would all read as "on" to a browser.
     *
     * @return array
     */
    public static function invalidValues(): array
    {
        return [
            'the string "false"'     => ['false'],
            'the string "true"'      => ['true'],
            'the string "0"'         => ['0'],
            'the string "1"'         => ['1'],
            'the string "no"'        => ['no'],
            'the string "off"'       => ['off'],
            'a space'                => [' '],
            'padded name'            => [' disabled '],
            'another boolean\'s name' => ['checked'],
            'integer one'            => [1],
            'integer zero'           => [0],
            'float'                  => [1.0],
            'array'                  => [[true]],
            'empty array'            => [[]],
        ];
    }//end invalidValues()

    /**
     * Tests that ambiguous values are refused, leaving the old state in place.
     *
     * @param mixed $value The value as given.
     *
     * @return void
     */
    #[DataProvider('invalidValues')]
    public function testRefusesAmbiguousValues(mixed $value): void
    {
        $attr = $this->attr()->setValue(true);

        try {
            $attr->setValue($value);
            $this->fail('An ambiguous value was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('"disabled"', $e->getMessage());
            $this->assertTrue($attr->getValue());
        }
    }//end testRefusesAmbiguousValues()

    /**
     * Tests that "its own name" follows the key, whatever case the key was set in.
     *
     * @return void
     */
    public function testOwnNameFollowsTheKey(): void
    {
        $attr = new BooleanAttribute();
        $attr->setKey('CHECKED');

        $this->assertSame('checked', $attr->setValue('checked')->render());

        $this->expectException(\InvalidArgumentException::class);

        $attr->setValue('disabled');
    }//end testOwnNameFollowsTheKey()
}//end class
