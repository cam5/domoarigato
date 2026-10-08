<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests `CollectionAttribute`
 */
#[CoversClass(\Cam5\Domoarigato\Attributes\CollectionAttribute::class)]
final class CollectionAttributeTest extends TestCase
{
    /**
     * The attribute under test.
     *
     * @var CollectionAttribute
     */
    private CollectionAttribute $attr;

    /**
     * Sets up an element between each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->attr = new CollectionAttribute();
        $this->attr->setKey('foo');
    }//end setUp()


    /**
     * Tests the key getters and setters of CollectionAttribute
     *
     * @return void
     */
    public function testGetAndSetKeys(): void
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
    public function testAddSingularValues(): void
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
    public function testIdempotencyOfAddingValues(): void
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
    public function testAddMultipleValues(): void
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
    public function testAddMultipleValuesWithoutAppending(): void
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
    public function testRemoveValues(): void
    {
        $this->attr->setKey('many')
            ->setValues(['multitude', 'myriad', 'mucho'], false);

        $this->attr->removeValue('myriad');

        $this->assertEquals(
            'many="multitude mucho"',
            $this->attr->render()
        );
    }//end testRemoveValues()

    /**
     * Tests that removing a value doesn't leave a gap behind.
     *
     * @return void
     */
    public function testRemovingReindexesValues(): void
    {
        $this->attr->setValues(['a', 'b', 'c'])->removeValue('a');

        $this->assertSame(['b', 'c'], $this->attr->getValues());
    }//end testRemovingReindexesValues()

    /**
     * Tests removing things that were never there.
     *
     * @return void
     */
    public function testRemovingMissingValuesIsHarmless(): void
    {
        $this->assertSame($this->attr, $this->attr->removeValue('nope'));
        $this->assertSame([], $this->attr->getValues());

        $this->attr->setValues('a b')->removeValue('c')->removeValue('')->removeValue('   ');

        $this->assertSame(['a', 'b'], $this->attr->getValues());
    }//end testRemovingMissingValuesIsHarmless()

    /**
     * Tests that several values can be removed in one go.
     *
     * @return void
     */
    public function testRemovesSeveralValuesAtOnce(): void
    {
        $this->attr->setValues('a b c d')->removeValue('d  b');

        $this->assertSame(['a', 'c'], $this->attr->getValues());
    }//end testRemovesSeveralValuesAtOnce()

    /**
     * Tests that an attribute without values renders nothing at all.
     *
     * @return void
     */
    public function testRendersNothingWhenEmpty(): void
    {
        $this->assertTrue($this->attr->isEmpty());
        $this->assertSame('', $this->attr->render());

        $this->attr->addValue('a');

        $this->assertFalse($this->attr->isEmpty());

        $this->attr->removeValue('a');

        $this->assertTrue($this->attr->isEmpty());
        $this->assertSame('', $this->attr->render());
    }//end testRendersNothingWhenEmpty()

    /**
     * Inputs, and the individual values they should break down into.
     *
     * @return array
     */
    public static function tokens(): array
    {
        return [
            'single'                 => ['a', ['a']],
            'two in a string'        => ['a b', ['a', 'b']],
            'extra spaces'           => ['  a   b  ', ['a', 'b']],
            'tabs and newlines'      => ["a\tb\nc\rd\fe", ['a', 'b', 'c', 'd', 'e']],
            'empty string'           => ['', []],
            'whitespace only'        => [" \t\n", []],
            'duplicates in a string' => ['a b a', ['a', 'b']],
            'array'                  => [['a', 'b'], ['a', 'b']],
            'array with duplicates'  => [['a', 'a', 'b'], ['a', 'b']],
            'array entry with space' => [['a b', 'c'], ['a', 'b', 'c']],
            'array with empties'     => [['', 'a', ' '], ['a']],
            'empty array'            => [[], []],
            'associative array'      => [['x' => 'a', 'y' => 'b'], ['a', 'b']],
            'null'                   => [null, []],
            'integer'                => [5, ['5']],
            'zero'                   => [0, ['0']],
            'the string "0"'         => ['0', ['0']],
            'float'                  => [1.5, ['1.5']],
            'array of numbers'       => [[1, 2.5, '3'], ['1', '2.5', '3']],
            'case sensitive'         => ['a A', ['a', 'A']],
            'non-breaking space'     => ["a\u{A0}b", ["a\u{A0}b"]],
            'multibyte'              => ['日本 語', ['日本', '語']],
            'tailwind-ish'           => ['md:w-1/2 hover:bg-[#fff]', ['md:w-1/2', 'hover:bg-[#fff]']],
        ];
    }//end tokens()

    /**
     * Tests how input is broken into values.
     *
     * @param mixed $input    The value as given.
     * @param array $expected The individual values we expect to hold.
     *
     * @return void
     */
    #[DataProvider('tokens')]
    public function testSplitsInputIntoValues(mixed $input, array $expected): void
    {
        $this->assertSame($expected, $this->attr->setValues($input)->getValues());
    }//end testSplitsInputIntoValues()

    /**
     * Tests that `setValue` takes everything `setValues` does.
     *
     * @param mixed $input    The value as given.
     * @param array $expected The individual values we expect to hold.
     *
     * @return void
     */
    #[DataProvider('tokens')]
    public function testSetValueSplitsInputToo(mixed $input, array $expected): void
    {
        $this->assertSame($expected, $this->attr->setValue($input)->getValues());
    }//end testSetValueSplitsInputToo()

    /**
     * Tests that strict comparison keeps "0" and "00" (and friends) apart.
     *
     * @return void
     */
    public function testLooseLookalikesAreDistinctValues(): void
    {
        $this->attr->setValues(['0', '00', '0.0', '1e1', '10', 'abc']);

        $this->assertSame(['0', '00', '0.0', '1e1', '10', 'abc'], $this->attr->getValues());

        $this->attr->removeValue(0);

        $this->assertSame(['00', '0.0', '1e1', '10', 'abc'], $this->attr->getValues());
    }//end testLooseLookalikesAreDistinctValues()

    /**
     * Tests that null adds nothing, but still clears when asked not to append.
     *
     * @return void
     */
    public function testNullClearsWhenNotAppending(): void
    {
        $this->attr->setValues('a b')->setValues(null);

        $this->assertSame(['a', 'b'], $this->attr->getValues());

        $this->attr->setValues(null, false);

        $this->assertSame([], $this->attr->getValues());
    }//end testNullClearsWhenNotAppending()

    /**
     * Tests looking for values.
     *
     * @return void
     */
    public function testHasValue(): void
    {
        $this->assertFalse($this->attr->hasValue('a'));

        $this->attr->setValues('a b 5');

        $this->assertTrue($this->attr->hasValue('a'));
        $this->assertTrue($this->attr->hasValue('b a'));
        $this->assertTrue($this->attr->hasValue(5));
        $this->assertTrue($this->attr->hasValue(' a '));
        $this->assertFalse($this->attr->hasValue('A'));
        $this->assertFalse($this->attr->hasValue('c'));
        $this->assertFalse($this->attr->hasValue('a c'));
        $this->assertFalse($this->attr->hasValue(''));
        $this->assertFalse($this->attr->hasValue('  '));
    }//end testHasValue()

    /**
     * Tests that values are escaped as a whole when rendered.
     *
     * @return void
     */
    public function testEscapesValues(): void
    {
        $this->attr->setValues(['"quoted"', '<b>', 'a&b', "it's"]);

        $this->assertSame(
            'foo="&quot;quoted&quot; &lt;b&gt; a&amp;b it&apos;s"',
            $this->attr->render()
        );
    }//end testEscapesValues()

    /**
     * Things that can't be a value.
     *
     * @return array
     */
    public static function invalidValues(): array
    {
        return [
            'true'               => [true],
            'false'              => [false],
            'infinity'           => [INF],
            'not a number'       => [NAN],
            'array with a bool'  => [['a', true]],
            'array with null'    => [['a', null]],
            'nested array'       => [['a', ['b']]],
            'array with object'  => [['a', new \stdClass()]],
            'array with NAN'     => [[NAN]],
        ];
    }//end invalidValues()

    /**
     * Tests that unusable values are refused.
     *
     * @param mixed $value The value as given.
     *
     * @return void
     */
    #[DataProvider('invalidValues')]
    public function testRefusesInvalidValues(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo"');

        $this->attr->setValues($value);
    }//end testRefusesInvalidValues()

    /**
     * Tests that the single-value methods refuse non-finite numbers as well.
     *
     * @return void
     */
    public function testSingleValueMethodsRefuseNonFiniteNumbers(): void
    {
        foreach (['addValue', 'removeValue', 'hasValue'] as $method) {
            try {
                $this->attr->$method(INF);
                $this->fail($method.' accepted INF.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('finite', $e->getMessage());
            }
        }
    }//end testSingleValueMethodsRefuseNonFiniteNumbers()

    /**
     * Tests that the fluent methods hand back the attribute.
     *
     * @return void
     */
    public function testIsFluent(): void
    {
        $this->assertSame($this->attr, $this->attr->setValue('a'));
        $this->assertSame($this->attr, $this->attr->setValues('b'));
        $this->assertSame($this->attr, $this->attr->addValue('c'));
        $this->assertSame($this->attr, $this->attr->removeValue('c'));
    }//end testIsFluent()
}//end class
