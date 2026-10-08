<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\ContentModels;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Enums\StaticEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the category and content model enums against a snapshot of the HTML Living Standard.
 *
 * As with the attributes, the expectations are worked out from the spec's own wording.
 */
#[CoversClass(Categories::class)]
#[CoversClass(ContentModels::class)]
#[CoversClass(StaticEnum::class)]
final class ContentModelsTest extends TestCase
{

    /**
     * How the spec words each thing an element may contain, and what we call it.
     *
     * @var array
     */
    const WORDING = [
        'flow'                                  => Categories::FLOW,
        'phrasing'                              => Categories::PHRASING,
        'heading content'                       => Categories::HEADING,
        'metadata content'                      => Categories::METADATA,
        'script-supporting elements'            => Categories::SCRIPT_SUPPORTING,
        'transparent'                           => ContentModels::TRANSPARENT,
        'text'                                  => ContentModels::TEXT,
        'script, data, or script documentation' => ContentModels::TEXT,
        'one img'                               => 'img',
        'per [SVG]'                             => ContentModels::ANYTHING,
        'per [MATHML]'                          => ContentModels::ANYTHING,
        // <noscript> "varies" with where it is; outside of a <head>, it is transparent.
        'varies'                                => ContentModels::TRANSPARENT,
    ];

    /**
     * The places where we knowingly differ from the spec's index.
     *
     * @var array
     */
    const OVERRIDES = [
        // Its children stand in for the template's contents, which may be anything.
        'template' => [ContentModels::ANYTHING],
    ];

    /**
     * The spec's two lists of categories don't quite agree with each other. Where they differ
     * we follow the table of categories, and these are the elements it concerns.
     *
     * @var array
     */
    const DISAGREEMENTS = [
        'hgroup'          => ['heading'],
        'object'          => ['interactive'],
        'selectedcontent' => ['phrasing'],
        'th'              => ['interactive'],
    ];

    /**
     * Reads the snapshot of the spec.
     *
     * @return array
     */
    private static function spec(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../fixtures/whatwg-content-models.json'), true);
    }//end spec()

    /**
     * Every category, with the elements the spec always puts in it, and those it sometimes does.
     *
     * @return array
     */
    public static function categories(): array
    {
        $elements = array_keys(Elements::all());
        $data     = [];

        foreach (self::spec()['categories'] as $category => $row) {
            // The row also names things that aren't HTML elements: "MathML", "Text", "autonomous custom elements".
            $always = array_values(array_intersect(explode(' ', implode(' ', $row['elements'])), $elements));

            preg_match_all('/(?:^|; )(\w+) \(/', $row['exceptions'], $matches);

            $sometimes = array_values(array_unique(array_intersect($matches[1], $elements)));

            $data[$category] = [$category, $always, $sometimes];
        }

        return $data;
    }//end categories()

    /**
     * Every element, with the spec's wording for what it may contain.
     *
     * @return array
     */
    public static function elements(): array
    {
        $data = [];

        foreach (self::spec()['elements'] as $name => $row) {
            $data[$name] = [$name, $row['children'], $row['categories']];
        }

        return $data;
    }//end elements()

    /**
     * Tests that the snapshot is the size we think it is.
     *
     * @return void
     */
    public function testSnapshotIsComplete(): void
    {
        $this->assertCount(9, self::categories());
        $this->assertCount(115, self::elements());
        $this->assertCount(83, self::categories()['flow'][1]);
        $this->assertSame(['area', 'link', 'main', 'meta'], self::categories()['flow'][2]);
    }//end testSnapshotIsComplete()

    /**
     * Tests that the enum of categories has the spec's categories, and a constant for each.
     *
     * @return void
     */
    public function testCategoriesMatchTheSpec(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(self::categories()), array_keys(Categories::all()));

        $constants = (new \ReflectionClass(Categories::class))->getConstants();

        $this->assertEqualsCanonicalizing(array_keys(Categories::all()), array_values($constants));

        foreach ($constants as $constant => $category) {
            $this->assertSame(strtoupper(str_replace('-', '_', $category)), $constant);
        }
    }//end testCategoriesMatchTheSpec()

    /**
     * Tests the membership of each category.
     *
     * @param string   $category  The category.
     * @param string[] $always    The elements that are always in it.
     * @param string[] $sometimes The elements that are in it under some condition.
     *
     * @return void
     */
    #[DataProvider('categories')]
    public function testCategoryHasTheRightElements(string $category, array $always, array $sometimes): void
    {
        $this->assertTrue(Categories::contains($category));
        $this->assertEqualsCanonicalizing($always, Categories::get($category));
        $this->assertSame(Categories::get($category), array_values(array_unique(Categories::get($category))));

        foreach (array_keys(Elements::all()) as $name) {
            $this->assertSame(in_array($name, $always, true), in_array($category, Categories::of($name), true), $name);
            $this->assertSame(
                in_array($name, $sometimes, true),
                in_array($category, Categories::conditionallyOf($name), true),
                $name
            );
        }

        // Nothing is both always and sometimes a member.
        $this->assertSame([], array_intersect($always, $sometimes));
    }//end testCategoryHasTheRightElements()

    /**
     * Tests looking up an element's categories.
     *
     * @return void
     */
    public function testCategoriesOfAnElement(): void
    {
        $this->assertSame(['flow', 'phrasing', 'palpable'], Categories::of('em'));
        $this->assertSame(['flow', 'phrasing', 'palpable'], Categories::of('EM'));
        $this->assertSame(['flow', 'sectioning', 'palpable'], Categories::of('section'));
        $this->assertSame(['flow', 'heading', 'palpable'], Categories::of('h1'));
        $this->assertSame(['metadata', 'flow', 'phrasing', 'script-supporting'], Categories::of('script'));
        $this->assertSame(['flow', 'phrasing', 'embedded', 'palpable'], Categories::of('video'));
        $this->assertSame(['interactive'], Categories::conditionallyOf('video'));
        $this->assertSame(['interactive'], Categories::conditionallyOf('VIDEO'));
        $this->assertSame(['flow', 'phrasing'], Categories::conditionallyOf('meta'));
        $this->assertSame(['metadata'], Categories::of('meta'));
        $this->assertSame(['palpable'], Categories::conditionallyOf('ul'));

        // Elements that only make sense inside of one particular parent have no category.
        foreach (['li', 'td', 'tr', 'body', 'head', 'html', 'option', 'caption', 'dt', 'source'] as $name) {
            $this->assertSame([], Categories::of($name), $name);
            $this->assertSame([], Categories::conditionallyOf($name), $name);
        }

        // Neither do names we've never heard of.
        $this->assertSame([], Categories::of('my-element'));
        $this->assertSame([], Categories::of(''));
        $this->assertSame([], Categories::conditionallyOf('my-element'));
    }//end testCategoriesOfAnElement()

    /**
     * Tests our categories against the second place the spec lists them: element by element.
     *
     * @param string   $name       The tag name.
     * @param string[] $children   Not used here.
     * @param string[] $categories The spec's wording for the element's categories.
     *
     * @return void
     */
    #[DataProvider('elements')]
    public function testCategoriesAgreeWithTheElementIndex(string $name, array $children, array $categories): void
    {
        $known    = array_keys(Categories::all());
        $expected = array_values(array_intersect(array_map(fn ($category) => rtrim($category, '*'), $categories), $known));
        $actual   = array_merge(Categories::of($name), Categories::conditionallyOf($name));

        $difference = array_values(array_merge(array_diff($expected, $actual), array_diff($actual, $expected)));

        $this->assertSame((self::DISAGREEMENTS[$name] ?? []), $difference);
    }//end testCategoriesAgreeWithTheElementIndex()

    /**
     * Tests that there's a content model for every element, and only for elements.
     *
     * @return void
     */
    public function testEveryElementHasAContentModel(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(Elements::all()), array_keys(ContentModels::all()));
        $this->assertFalse(ContentModels::contains('my-element'));
        $this->assertFalse(ContentModels::contains(ContentModels::TEXT));
    }//end testEveryElementHasAContentModel()

    /**
     * Tests each element's content model against the spec's wording for it.
     *
     * @param string   $name     The tag name.
     * @param string[] $children The spec's wording for what the element may contain.
     *
     * @return void
     */
    #[DataProvider('elements')]
    public function testContentModelMatchesTheSpec(string $name, array $children): void
    {
        $expected = [];

        foreach ($children as $wording) {
            $wording = rtrim($wording, '*');

            if ('empty' !== $wording) {
                $expected[] = (self::WORDING[$wording] ?? $wording);
            }
        }

        $expected = (self::OVERRIDES[$name] ?? array_values(array_unique($expected)));
        $model    = ContentModels::get($name);

        $this->assertSame($expected, $model);
        $this->assertSame($model, ContentModels::get(strtoupper($name)));

        // Every entry is a marker, a category or an element: nothing misspelled.
        $markers = [ContentModels::TEXT, ContentModels::TRANSPARENT, ContentModels::ANYTHING];

        foreach ($model as $entry) {
            $this->assertTrue(
                in_array($entry, $markers, true) || Categories::contains($entry) || Elements::contains($entry),
                $entry
            );
        }
    }//end testContentModelMatchesTheSpec()

    /**
     * Tests a few models by hand, as a second opinion on all of the above.
     *
     * @return void
     */
    public function testWellKnownContentModels(): void
    {
        $this->assertSame(['li', 'script-supporting'], ContentModels::get('ul'));
        $this->assertSame(['phrasing'], ContentModels::get('p'));
        $this->assertSame(['flow'], ContentModels::get('div'));
        $this->assertSame(['head', 'body'], ContentModels::get('html'));
        $this->assertSame(['th', 'td', 'script-supporting'], ContentModels::get('tr'));
        $this->assertSame(['#transparent'], ContentModels::get('a'));
        $this->assertSame(['#text'], ContentModels::get('title'));
        $this->assertSame(['#anything'], ContentModels::get('svg'));
        $this->assertSame([], ContentModels::get('br'));
        $this->assertSame([], ContentModels::get('iframe'));
    }//end testWellKnownContentModels()

    /**
     * Tests that every void element has an empty content model.
     *
     * @return void
     */
    public function testVoidElementsMayContainNothing(): void
    {
        foreach (ElementsTest::VOID_ELEMENTS as $name) {
            $this->assertSame([], ContentModels::get($name), $name);
        }
    }//end testVoidElementsMayContainNothing()

    /**
     * Tests that the markers can't be mistaken for an element or a category.
     *
     * @return void
     */
    public function testMarkersAreDistinct(): void
    {
        foreach ([ContentModels::TEXT, ContentModels::TRANSPARENT, ContentModels::ANYTHING] as $marker) {
            $this->assertStringStartsWith('#', $marker);
            $this->assertFalse(Elements::contains($marker));
            $this->assertFalse(Categories::contains($marker));
        }

        // And no category shares its name with an element.
        $this->assertSame([], array_intersect(array_keys(Categories::all()), array_keys(Elements::all())));
    }//end testMarkersAreDistinct()

    /**
     * Tests that asking for the model of something that isn't an element is an error.
     *
     * @return void
     */
    public function testUnknownElementsHaveNoContentModel(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Could not find "my-element" key in '.ContentModels::class.' enum.');

        ContentModels::get('my-element');
    }//end testUnknownElementsHaveNoContentModel()
}//end class
