<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\AttributeInterface;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Attributes\Traits\HasKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests `SimpleAttribute`
 */
#[CoversClass(SimpleAttribute::class)]
#[CoversTrait(HasKey::class)]
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

        $this->assertSame('', $attr->getKey());

        $attr->setKey('foo');

        $this->assertEquals(
            'foo',
            $attr->getKey()
        );
    }//end testGetAndSetKeys()

    /**
     * Tests that it honours the contract every attribute shares.
     *
     * @return void
     */
    public function testImplementsTheInterface(): void
    {
        $this->assertInstanceOf(AttributeInterface::class, new SimpleAttribute());
    }//end testImplementsTheInterface()

    /**
     * Tests that keys are stored in lowercase.
     *
     * @return void
     */
    public function testKeysAreLowercased(): void
    {
        $attr = new SimpleAttribute();

        $this->assertSame('tabindex', $attr->setKey('TabIndex')->getKey());
    }//end testKeysAreLowercased()

    /**
     * Tests that a key that would break the markup never gets stored.
     *
     * @return void
     */
    public function testInvalidKeysAreRefused(): void
    {
        $attr = new SimpleAttribute();
        $attr->setKey('safe');

        try {
            $attr->setKey('x onclick="alert(1)"');
            $this->fail('An invalid key was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('safe', $attr->getKey());
        }
    }//end testInvalidKeysAreRefused()

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

    /**
     * Values, what's stored for them, and what gets rendered.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            'string'             => ['bar', 'bar', 'foo="bar"'],
            'empty string'       => ['', '', 'foo=""'],
            'whitespace only'    => ['  ', '  ', 'foo="  "'],
            'the string "0"'     => ['0', '0', 'foo="0"'],
            'the string "false"' => ['false', 'false', 'foo="false"'],
            'integer'            => [42, '42', 'foo="42"'],
            'zero'               => [0, '0', 'foo="0"'],
            'negative integer'   => [-1, '-1', 'foo="-1"'],
            'float'              => [0.5, '0.5', 'foo="0.5"'],
            'whole float'        => [2.0, '2', 'foo="2"'],
            'negative float'     => [-1.25, '-1.25', 'foo="-1.25"'],
            'true'               => [true, true, 'foo'],
            'false'              => [false, false, ''],
            'null'               => [null, null, ''],
            'double quotes'      => ['a "b" c', 'a "b" c', 'foo="a &quot;b&quot; c"'],
            'single quotes'      => ["a 'b' c", "a 'b' c", 'foo="a &apos;b&apos; c"'],
            'ampersand'          => ['a & b', 'a & b', 'foo="a &amp; b"'],
            'markup'             => ['<script>', '<script>', 'foo="&lt;script&gt;"'],
            'breakout attempt'   => ['"><script>alert(1)</script>', '"><script>alert(1)</script>', 'foo="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"'],
            'multibyte'          => ['日本語', '日本語', 'foo="日本語"'],
            'multiline'          => ["a\nb", "a\nb", "foo=\"a\nb\""],
        ];
    }//end values()

    /**
     * Tests how each kind of value is stored and rendered.
     *
     * @param mixed  $value    The value as given.
     * @param mixed  $stored   The value we expect to get back.
     * @param string $rendered The HTML we expect.
     *
     * @return void
     */
    #[DataProvider('values')]
    public function testStoresAndRendersValues(mixed $value, mixed $stored, string $rendered): void
    {
        $attr = new SimpleAttribute();
        $attr->setKey('foo');

        $this->assertSame($attr, $attr->setValue($value));
        $this->assertSame($stored, $attr->getValue());
        $this->assertSame($rendered, $attr->render());
        $this->assertSame(('' === $rendered), $attr->isEmpty());
    }//end testStoresAndRendersValues()

    /**
     * Tests that an attribute nobody gave a value to stays out of the way.
     *
     * @return void
     */
    public function testIsEmptyUntilGivenAValue(): void
    {
        $attr = new SimpleAttribute();
        $attr->setKey('foo');

        $this->assertNull($attr->getValue());
        $this->assertTrue($attr->isEmpty());
        $this->assertSame('', $attr->render());
    }//end testIsEmptyUntilGivenAValue()

    /**
     * Tests that a later value replaces an earlier one.
     *
     * @return void
     */
    public function testValuesAreReplaced(): void
    {
        $attr = new SimpleAttribute();
        $attr->setKey('foo')->setValue('bar')->setValue('baz');

        $this->assertSame('foo="baz"', $attr->render());

        $attr->setValue(null);

        $this->assertSame('', $attr->render());
    }//end testValuesAreReplaced()

    /**
     * Values a single attribute can't hold.
     *
     * @return array
     */
    public static function invalidValues(): array
    {
        return [
            'array'             => [['a', 'b']],
            'empty array'       => [[]],
            'infinity'          => [INF],
            'negative infinity' => [-INF],
            'not a number'      => [NAN],
        ];
    }//end invalidValues()

    /**
     * Tests that unusable values are refused, leaving the old value in place.
     *
     * @param mixed $value The value as given.
     *
     * @return void
     */
    #[DataProvider('invalidValues')]
    public function testRefusesInvalidValues(mixed $value): void
    {
        $attr = new SimpleAttribute();
        $attr->setKey('foo')->setValue('bar');

        try {
            $attr->setValue($value);
            $this->fail('An invalid value was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('"foo"', $e->getMessage());
            $this->assertSame('bar', $attr->getValue());
        }
    }//end testRefusesInvalidValues()
}//end class
