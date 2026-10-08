<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\BooleanAttribute;
use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Attributes\CommaSeparatedAttribute;
use Cam5\Domoarigato\Attributes\OnOffAttribute;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Attributes\TrueFalseAttribute;
use Cam5\Domoarigato\Attributes\YesNoAttribute;
use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Enums\Attributes;
use Cam5\Domoarigato\Factories\AttributeFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the attribute enum against a snapshot of the HTML Living Standard's attribute index.
 *
 * The expectations here are worked out from the spec's own description of each attribute's
 * value, not read back from the enum, so a typo or a wrong class in the enum fails a test.
 */
#[CoversClass(Attributes::class)]
#[CoversClass(AttributeFactory::class)]
final class AttributesTest extends TestCase
{

    /**
     * The places where we knowingly differ from a literal reading of the spec's value column.
     *
     * @var array
     */
    const OVERRIDES = [
        // "on" / "off" on a form, a list of autofill tokens on a control: a token list fits both.
        'autocomplete' => CollectionAttribute::class,
        // "Valid list of floating-point numbers", which is to say comma separated.
        'coords'       => CommaSeparatedAttribute::class,
        // Keyword attributes, whose two main states get written out for booleans.
        'translate'    => YesNoAttribute::class,
        'autocorrect'  => OnOffAttribute::class,
    ];

    /**
     * Reads the snapshot of the spec.
     *
     * @return array
     */
    private static function spec(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../fixtures/whatwg-attributes.json'), true);
    }//end spec()

    /**
     * Works out which class an attribute should get, from how the spec describes its value.
     *
     * An attribute that means different things on different elements ("for" is an ID on a label,
     * but a list of IDs on an output) can only safely be a simple attribute.
     *
     * @param string   $name   The attribute's name.
     * @param string[] $values The spec's description of its value, once per element it's defined on.
     *
     * @return string
     */
    private static function expectedClass(string $name, array $values): string
    {
        if (true === array_key_exists($name, self::OVERRIDES)) {
            return self::OVERRIDES[$name];
        }

        $every = function (callable $test) use ($values): bool {
            return count($values) === count(array_filter($values, $test));
        };

        if (true === $every(fn ($value) => 'Boolean attribute' === $value)) {
            return BooleanAttribute::class;
        }

        if (true === $every(fn ($value) => str_contains($value, 'space-separated tokens'))) {
            return CollectionAttribute::class;
        }

        if (true === $every(fn ($value) => str_contains(strtolower($value), 'comma-separated'))) {
            return CommaSeparatedAttribute::class;
        }

        if (true === $every(fn ($value) => str_starts_with($value, '"true"; "false"'))) {
            return TrueFalseAttribute::class;
        }

        return SimpleAttribute::class;
    }//end expectedClass()

    /**
     * Every attribute in the spec, with the class we expect for it.
     *
     * @return array
     */
    public static function specAttributes(): array
    {
        $spec = self::spec();
        $data = [];

        foreach ($spec['attributes'] as $name => $values) {
            $data[$name] = [$name, self::expectedClass($name, $values)];
        }

        foreach ($spec['eventHandlers'] as $name) {
            $data[$name] = [$name, SimpleAttribute::class];
        }

        return $data;
    }//end specAttributes()

    /**
     * Tests that the snapshot is the size we think it is, so a truncated file can't pass quietly.
     *
     * @return void
     */
    public function testSnapshotIsComplete(): void
    {
        $spec = self::spec();

        $this->assertCount(144, $spec['attributes']);
        $this->assertCount(89, $spec['eventHandlers']);
        $this->assertCount(233, self::specAttributes());
    }//end testSnapshotIsComplete()

    /**
     * Tests that the enum has every attribute in the spec, and nothing else.
     *
     * @return void
     */
    public function testEnumMatchesTheSpecExactly(): void
    {
        $expected = array_keys(self::specAttributes());
        $actual   = array_keys(Attributes::all());

        sort($expected);
        sort($actual);

        $this->assertSame([], array_values(array_diff($expected, $actual)), 'Missing from the enum.');
        $this->assertSame([], array_values(array_diff($actual, $expected)), 'Not in the spec.');
        $this->assertSame($expected, $actual);
    }//end testEnumMatchesTheSpecExactly()

    /**
     * Tests that there's a constant for every key, and a key for every constant.
     *
     * @return void
     */
    public function testConstantsAndKeysLineUp(): void
    {
        $constants = (new \ReflectionClass(Attributes::class))->getConstants();

        $this->assertSame(count($constants), count(array_unique($constants)));
        $this->assertEqualsCanonicalizing(array_keys(Attributes::all()), array_values($constants));

        foreach ($constants as $constant => $name) {
            if ('class' === $name) {
                // "CLASS" can't be a constant name in PHP.
                $this->assertSame('ATTR_CLASS', $constant);
                continue;
            }

            $this->assertSame(strtoupper(str_replace('-', '_', $name)), $constant);
        }
    }//end testConstantsAndKeysLineUp()

    /**
     * Tests that every attribute is mapped to the class its spec definition calls for.
     *
     * @param string $name  The attribute's name.
     * @param string $class The class we expect.
     *
     * @return void
     */
    #[DataProvider('specAttributes')]
    public function testAttributeIsMappedToTheRightClass(string $name, string $class): void
    {
        $this->assertTrue(Attributes::contains($name));
        $this->assertTrue(Attributes::contains(strtoupper($name)));
        $this->assertSame($class, Attributes::get($name));

        $attr = AttributeFactory::createFromName($name);

        $this->assertSame($class, get_class($attr));
        $this->assertSame($name, $attr->getKey());
        $this->assertTrue($attr->isEmpty());
        $this->assertSame('', $attr->render());
    }//end testAttributeIsMappedToTheRightClass()

    /**
     * Tests how every attribute renders on an element, for the values its kind cares about.
     *
     * @param string $name  The attribute's name.
     * @param string $class The class we expect.
     *
     * @return void
     */
    #[DataProvider('specAttributes')]
    public function testAttributeRendersOnAnElement(string $name, string $class): void
    {
        $render = function (mixed $value) use ($name): string {
            return Domo::createElement('div')->addAttribute($name, $value)->render();
        };

        // Nobody's attribute renders for null.
        $this->assertSame('<div></div>', $render(null));

        switch ($class) {
            case BooleanAttribute::class:
                $this->assertSame('<div '.$name.'></div>', $render(true));
                $this->assertSame('<div '.$name.'></div>', $render(''));
                $this->assertSame('<div '.$name.'></div>', $render($name));
                $this->assertSame('<div></div>', $render(false));
                break;

            case CollectionAttribute::class:
                $this->assertSame('<div '.$name.'="a b"></div>', $render('a  b a'));
                $this->assertSame('<div '.$name.'="a b"></div>', $render(['a', 'b']));
                $this->assertSame('<div></div>', $render(''));
                break;

            case CommaSeparatedAttribute::class:
                $this->assertSame('<div '.$name.'="a, b"></div>', $render('a,b,a'));
                $this->assertSame('<div '.$name.'="a, b"></div>', $render(['a', 'b']));
                $this->assertSame('<div></div>', $render(''));
                break;

            case TrueFalseAttribute::class:
                $this->assertSame('<div '.$name.'="true"></div>', $render(true));
                $this->assertSame('<div '.$name.'="false"></div>', $render(false));
                $this->assertSame('<div '.$name.'=""></div>', $render(''));
                break;

            case YesNoAttribute::class:
                $this->assertSame('<div '.$name.'="yes"></div>', $render(true));
                $this->assertSame('<div '.$name.'="no"></div>', $render(false));
                break;

            case OnOffAttribute::class:
                $this->assertSame('<div '.$name.'="on"></div>', $render(true));
                $this->assertSame('<div '.$name.'="off"></div>', $render(false));
                break;

            default:
                $this->assertSame(SimpleAttribute::class, $class);
                $this->assertSame('<div '.$name.'="a &amp; &quot;b&quot;"></div>', $render('a & "b"'));
                $this->assertSame('<div '.$name.'=""></div>', $render(''));
                $this->assertSame('<div '.$name.'="5"></div>', $render(5));
                $this->assertSame('<div '.$name.'></div>', $render(true));
                $this->assertSame('<div></div>', $render(false));
                break;
        }//end switch
    }//end testAttributeRendersOnAnElement()

    /**
     * The boolean attributes, written out by hand as a second opinion on the snapshot.
     *
     * @return array
     */
    public static function booleanAttributes(): array
    {
        $names = [
            'allowfullscreen', 'alpha', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default',
            'defer', 'disabled', 'formnovalidate', 'headingreset', 'inert', 'ismap', 'itemscope', 'loop',
            'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'playsinline', 'readonly', 'required',
            'reversed', 'selected', 'shadowrootclonable', 'shadowrootcustomelementregistry',
            'shadowrootdelegatesfocus', 'shadowrootserializable',
        ];

        return array_combine($names, array_map(fn ($name) => [$name], $names));
    }//end booleanAttributes()

    /**
     * Tests that the classic mistakes with boolean attributes are caught.
     *
     * @param string $name The attribute's name.
     *
     * @return void
     */
    #[DataProvider('booleanAttributes')]
    public function testBooleanAttributesRefuseStringsThatLookFalse(string $name): void
    {
        $this->assertSame(BooleanAttribute::class, Attributes::get($name));

        $el = Domo::createElement('div');

        foreach (['false', '0', 'no', 'off', 'true', 0, 1] as $value) {
            try {
                $el->addAttribute($name, $value);
                $this->fail('Accepted '.var_export($value, true).'.');
            } catch (\InvalidArgumentException $e) {
                $this->assertFalse($el->hasAttribute($name));
            }
        }
    }//end testBooleanAttributesRefuseStringsThatLookFalse()

    /**
     * Tests that exactly the hand-written list is boolean: no more, no fewer.
     *
     * @return void
     */
    public function testNothingElseIsBoolean(): void
    {
        $boolean = array_keys(array_filter(Attributes::all(), fn ($class) => BooleanAttribute::class === $class));

        $this->assertEqualsCanonicalizing(array_keys(self::booleanAttributes()), $boolean);
    }//end testNothingElseIsBoolean()

    /**
     * Tests the attributes that look boolean, but aren't.
     *
     * @return void
     */
    public function testLookalikesAreNotBoolean(): void
    {
        // "hidden" has an "until-found" state, and "download" may carry a filename.
        foreach (['hidden', 'download'] as $name) {
            $el = Domo::createElement('a');

            $this->assertSame('<a '.$name.'></a>', $el->addAttribute($name, true)->render());
            $this->assertSame('<a></a>', $el->addAttribute($name, false)->render());
            $this->assertSame('<a '.$name.'="x"></a>', $el->addAttribute($name, 'x')->render());
        }

        // Leaving these off doesn't mean "false", so false has to be written.
        foreach (['contenteditable', 'draggable', 'spellcheck', 'writingsuggestions'] as $name) {
            $this->assertSame(
                '<p '.$name.'="false"></p>',
                Domo::createElement('p')->addAttribute($name, false)->render()
            );
        }
    }//end testLookalikesAreNotBoolean()

    /**
     * Tests the attributes whose meaning depends on the element stay simple.
     *
     * @return void
     */
    public function testAmbiguousAttributesAreSimple(): void
    {
        $this->assertSame(SimpleAttribute::class, Attributes::get('for'));
        $this->assertSame(SimpleAttribute::class, Attributes::get('sizes'));

        $this->assertSame(
            '<output for="a b"></output>',
            Domo::createElement('output')->addAttribute('for', 'a b')->render()
        );
        $this->assertSame(
            '<img sizes="(max-width: 600px) 480px, 800px" />',
            Domo::createElement('img')->addAttribute('sizes', '(max-width: 600px) 480px, 800px')->render()
        );
    }//end testAmbiguousAttributesAreSimple()

    /**
     * Tests that names outside the spec still work, as simple attributes.
     *
     * @return void
     */
    public function testUnknownAttributesAreSimple(): void
    {
        foreach (['data-disabled', 'aria-disabled', 'x-data', 'disabled-ish', 'classname'] as $name) {
            $this->assertFalse(Attributes::contains($name));
            $this->assertSame(SimpleAttribute::class, get_class(AttributeFactory::createFromName($name)));
        }
    }//end testUnknownAttributesAreSimple()
}//end class
