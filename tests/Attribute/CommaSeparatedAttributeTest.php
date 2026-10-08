<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Attributes\CommaSeparatedAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests `CommaSeparatedAttribute`
 */
#[CoversClass(CommaSeparatedAttribute::class)]
#[CoversClass(CollectionAttribute::class)]
final class CommaSeparatedAttributeTest extends TestCase
{
    /**
     * Creates an "accept" attribute to test with.
     *
     * @return CommaSeparatedAttribute
     */
    private function attr(): CommaSeparatedAttribute
    {
        $attr = new CommaSeparatedAttribute();

        return $attr->setKey('accept');
    }//end attr()

    /**
     * Tests that it's a collection like any other.
     *
     * @return void
     */
    public function testIsACollection(): void
    {
        $this->assertInstanceOf(CollectionAttribute::class, $this->attr());
    }//end testIsACollection()

    /**
     * Inputs, and the individual values they should break down into.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            'single'                     => ['image/png', ['image/png']],
            'comma separated'            => ['image/png,image/jpeg', ['image/png', 'image/jpeg']],
            'comma and space'            => ['image/png, image/jpeg', ['image/png', 'image/jpeg']],
            'messy whitespace'           => [" image/png ,\n\timage/jpeg ", ['image/png', 'image/jpeg']],
            'spaces stay inside a value' => ['a.jpg 1x, b.jpg 2x', ['a.jpg 1x', 'b.jpg 2x']],
            'empty string'               => ['', []],
            'only commas'                => [' , ,, ', []],
            'trailing comma'             => ['a,b,', ['a', 'b']],
            'leading comma'              => [',a', ['a']],
            'duplicates'                 => ['a, b, a', ['a', 'b']],
            'array'                      => [['a', 'b'], ['a', 'b']],
            'array entries are trimmed'  => [[' a ', "\tb\n"], ['a', 'b']],
            'array entry keeps a comma'  => [['/img,w_100/a.jpg 1x', 'b.jpg 2x'], ['/img,w_100/a.jpg 1x', 'b.jpg 2x']],
            'array with empties'         => [['', ' ', 'a'], ['a']],
            'null'                       => [null, []],
            'numbers'                    => [[0, 0, 10.5, 20], ['0', '10.5', '20']],
            'integer'                    => [7, ['7']],
        ];
    }//end values()

    /**
     * Tests how input is broken into values.
     *
     * @param mixed $input    The value as given.
     * @param array $expected The individual values we expect to hold.
     *
     * @return void
     */
    #[DataProvider('values')]
    public function testSplitsInputIntoValues(mixed $input, array $expected): void
    {
        $this->assertSame($expected, $this->attr()->setValues($input)->getValues());
    }//end testSplitsInputIntoValues()

    /**
     * Tests the comma-and-space rendering.
     *
     * @return void
     */
    public function testRendersWithCommas(): void
    {
        $attr = $this->attr()->setValues('image/png,image/jpeg')->addValue('.pdf');

        $this->assertSame('accept="image/png, image/jpeg, .pdf"', $attr->render());
    }//end testRendersWithCommas()

    /**
     * Tests that nothing is rendered without values.
     *
     * @return void
     */
    public function testRendersNothingWhenEmpty(): void
    {
        $this->assertSame('', $this->attr()->render());
        $this->assertSame('', $this->attr()->setValues(',')->render());
    }//end testRendersNothingWhenEmpty()

    /**
     * Tests the single-value methods with comma separated input.
     *
     * @return void
     */
    public function testAddRemoveAndHas(): void
    {
        $attr = $this->attr()->addValue('a, b, c');

        $this->assertTrue($attr->hasValue('a'));
        $this->assertTrue($attr->hasValue('c, a'));
        $this->assertFalse($attr->hasValue('a b'));
        $this->assertFalse($attr->hasValue(','));

        $attr->removeValue('a,c');

        $this->assertSame(['b'], $attr->getValues());
    }//end testAddRemoveAndHas()

    /**
     * Tests that a value with a comma of its own renders intact, and escaped.
     *
     * @return void
     */
    public function testValuesWithCommasSurviveAsArrayEntries(): void
    {
        $attr = new CommaSeparatedAttribute();
        $attr->setKey('srcset')->setValues(['/i,w_100/a.jpg?x=1&y=2 1x', '/i,w_200/a.jpg 2x']);

        $this->assertSame(
            'srcset="/i,w_100/a.jpg?x=1&amp;y=2 1x, /i,w_200/a.jpg 2x"',
            $attr->render()
        );
    }//end testValuesWithCommasSurviveAsArrayEntries()

    /**
     * Tests that booleans are still refused.
     *
     * @return void
     */
    public function testRefusesInvalidValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->attr()->setValues([true]);
    }//end testRefusesInvalidValues()
}//end class
