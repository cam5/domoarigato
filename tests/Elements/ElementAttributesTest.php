<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\CollectionAttribute;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\AbstractElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the attribute methods that every element shares.
 */
#[CoversClass(AbstractElement::class)]
final class ElementAttributesTest extends TestCase
{
    /**
     * One of each kind of element: enclosing, self-enclosing and generic.
     *
     * @return array
     */
    public static function tagNames(): array
    {
        return [
            'enclosing'      => ['div', '<div%s></div>'],
            'self-enclosing' => ['input', '<input%s />'],
            'generic'        => ['my-element', '<my-element%s></my-element>'],
        ];
    }//end tagNames()

    /**
     * Tests the README's example.
     *
     * @return void
     */
    public function testIdAndClassHelpers(): void
    {
        $div = Domo::createElement('div');

        $div->setId('foo')
            ->addClass('bar');

        $this->assertSame('<div id="foo" class="bar"></div>', $div->render());
    }//end testIdAndClassHelpers()

    /**
     * Tests that attributes come out in the order they went in.
     *
     * @param string $tagName The element to create.
     * @param string $format  How that element renders, with a placeholder for its attributes.
     *
     * @return void
     */
    #[DataProvider('tagNames')]
    public function testAttributesRenderInInsertionOrder(string $tagName, string $format): void
    {
        $el = Domo::createElement($tagName);

        $el->addAttribute('title', 'b')
            ->addAttribute('id', 'a')
            ->addAttribute('lang', 'en');

        $this->assertSame(sprintf($format, ' title="b" id="a" lang="en"'), $el->render());
    }//end testAttributesRenderInInsertionOrder()

    /**
     * Tests that an element without attributes has no stray whitespace.
     *
     * @param string $tagName The element to create.
     * @param string $format  How that element renders, with a placeholder for its attributes.
     *
     * @return void
     */
    #[DataProvider('tagNames')]
    public function testRendersWithoutAttributes(string $tagName, string $format): void
    {
        $el = Domo::createElement($tagName);

        $this->assertSame('', $el->renderAttrs());
        $this->assertSame(sprintf($format, ''), $el->render());
    }//end testRendersWithoutAttributes()

    /**
     * Tests that attributes which are "off" don't leave gaps, or get rendered.
     *
     * @param string $tagName The element to create.
     * @param string $format  How that element renders, with a placeholder for its attributes.
     *
     * @return void
     */
    #[DataProvider('tagNames')]
    public function testEmptyAttributesAreLeftOut(string $tagName, string $format): void
    {
        $el = Domo::createElement($tagName);

        $el->addAttribute('hidden', false)
            ->addAttribute('id', 'a')
            ->addAttribute('class', [])
            ->addAttribute('title', null)
            ->addAttribute('lang', 'en')
            ->addAttribute('download', null);

        $this->assertSame(sprintf($format, ' id="a" lang="en"'), $el->render());

        $el->removeAttribute('id')->removeAttribute('lang');

        $this->assertSame(sprintf($format, ''), $el->render());
    }//end testEmptyAttributesAreLeftOut()

    /**
     * Tests the values an attribute can be added with.
     *
     * @return void
     */
    public function testAddsEachKindOfValue(): void
    {
        $el = Domo::createElement('a');

        $el->addAttribute('href', '/?a=1&b=2')
            ->addAttribute('tabindex', -1)
            ->addAttribute('data-ratio', 1.5)
            ->addAttribute('download', true)
            ->addAttribute('title', '')
            ->addAttribute('class', ['btn', 'btn-primary']);

        $this->assertSame(
            '<a href="/?a=1&amp;b=2" tabindex="-1" data-ratio="1.5" download title="" class="btn btn-primary"></a>',
            $el->render()
        );
    }//end testAddsEachKindOfValue()

    /**
     * Tests that values can't break out of the tag.
     *
     * @return void
     */
    public function testAttributeValuesAreEscaped(): void
    {
        $el = Domo::createElement('div');

        $el->addAttribute('title', '"><script>alert(1)</script>')
            ->addClass('"><img src=x onerror=alert(1)>');

        $this->assertSame(
            '<div title="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;" class="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;"></div>',
            $el->render()
        );
    }//end testAttributeValuesAreEscaped()

    /**
     * Tests that adding an attribute twice replaces it, without moving it.
     *
     * @return void
     */
    public function testAddingAgainReplaces(): void
    {
        $el = Domo::createElement('div');

        $el->addAttribute('id', 'a')
            ->addAttribute('title', 't')
            ->addAttribute('id', 'b');

        $this->assertCount(2, $el->getAttributes());
        $this->assertSame('<div id="b" title="t"></div>', $el->render());

        // Even collections start over: `addAttribute` sets, `addClass` appends.
        $el->addAttribute('class', 'one')->addAttribute('class', 'two');

        $this->assertSame('<div id="b" title="t" class="two"></div>', $el->render());
    }//end testAddingAgainReplaces()

    /**
     * Tests that attribute names are case-insensitive everywhere.
     *
     * @return void
     */
    public function testNamesAreCaseInsensitive(): void
    {
        $el = Domo::createElement('div');

        $el->addAttribute('ID', 'a')->addAttribute('Id', 'b');

        $this->assertSame(['id'], array_keys($el->getAttributes()));
        $this->assertTrue($el->hasAttribute('iD'));
        $this->assertSame('b', $el->getAttribute('ID')->getValue());
        $this->assertSame('b', $el->getId());
        $this->assertSame('<div id="b"></div>', $el->render());

        $el->removeAttribute('ID');

        $this->assertSame('<div></div>', $el->render());
    }//end testNamesAreCaseInsensitive()

    /**
     * Tests that invalid names are refused, and leave the element untouched.
     *
     * @return void
     */
    public function testRefusesInvalidNames(): void
    {
        $el = Domo::createElement('div');

        foreach (['', 'a b', 'x="y"', 'on>click', "a\0"] as $name) {
            try {
                $el->addAttribute($name, 'value');
                $this->fail('An invalid name was accepted.');
            } catch (\InvalidArgumentException $e) {
                $this->assertCount(0, $el->getAttributes());
            }
        }

        $this->assertSame('<div></div>', $el->render());
    }//end testRefusesInvalidNames()

    /**
     * Tests that a refused value leaves the attribute that was there alone.
     *
     * @return void
     */
    public function testRefusedValuesLeaveTheOldAttribute(): void
    {
        $el = Domo::createElement('div')->addAttribute('id', 'keep');

        try {
            $el->addAttribute('id', ['a']);
            $this->fail('An invalid value was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('<div id="keep"></div>', $el->render());
        }
    }//end testRefusedValuesLeaveTheOldAttribute()

    /**
     * Tests that `setAttribute` is just another name for `addAttribute`.
     *
     * @return void
     */
    public function testSetAttributeIsAnAlias(): void
    {
        $el = Domo::createElement('div');

        $this->assertSame($el, $el->setAttribute('id', 'a'));
        $this->assertSame($el, $el->setAttribute('ID', 'b'));
        $this->assertSame('<div id="b"></div>', $el->render());
    }//end testSetAttributeIsAnAlias()

    /**
     * Tests fetching attribute objects.
     *
     * @return void
     */
    public function testGetAttribute(): void
    {
        $el = Domo::createElement('div')->addAttribute('title', 't')->addAttribute('class', 'c');

        $this->assertNull($el->getAttribute('id'));
        $this->assertNull($el->getAttribute(''));
        $this->assertInstanceOf(SimpleAttribute::class, $el->getAttribute('title'));
        $this->assertInstanceOf(CollectionAttribute::class, $el->getAttribute('class'));

        // It's the live object: changing it changes the element.
        $el->getAttribute('title')->setValue('changed');

        $this->assertSame('<div title="changed" class="c"></div>', $el->render());
    }//end testGetAttribute()

    /**
     * Tests that an attribute only counts as present when it will render.
     *
     * @return void
     */
    public function testHasAttribute(): void
    {
        $el = Domo::createElement('div');

        $this->assertFalse($el->hasAttribute('id'));

        $el->addAttribute('id', 'a')
            ->addAttribute('title', '')
            ->addAttribute('lang', null)
            ->addAttribute('hidden', false)
            ->addAttribute('download', true)
            ->addAttribute('class', '');

        $this->assertTrue($el->hasAttribute('id'));
        $this->assertTrue($el->hasAttribute('title'));
        $this->assertTrue($el->hasAttribute('download'));
        $this->assertFalse($el->hasAttribute('lang'));
        $this->assertFalse($el->hasAttribute('hidden'));
        $this->assertFalse($el->hasAttribute('class'));
        $this->assertFalse($el->hasAttribute(''));
    }//end testHasAttribute()

    /**
     * Tests removing attributes, including ones that were never there.
     *
     * @return void
     */
    public function testRemoveAttribute(): void
    {
        $el = Domo::createElement('div')->addAttribute('id', 'a')->addAttribute('title', 't');

        $this->assertSame($el, $el->removeAttribute('id'));
        $this->assertSame($el, $el->removeAttribute('id'));
        $this->assertSame($el, $el->removeAttribute('never-there'));
        $this->assertSame($el, $el->removeAttribute(''));
        $this->assertSame(['title'], array_keys($el->getAttributes()));
        $this->assertSame('<div title="t"></div>', $el->render());
    }//end testRemoveAttribute()

    /**
     * Tests the id helpers.
     *
     * @return void
     */
    public function testIdHelpers(): void
    {
        $el = Domo::createElement('div');

        $this->assertNull($el->getId());
        $this->assertSame($el, $el->setId('a'));
        $this->assertSame('a', $el->getId());
        $this->assertSame('b', $el->setId('b')->getId());
        $this->assertSame('<div id="b"></div>', $el->render());
        $this->assertSame('', $el->setId('')->getId());

        // An id without a string value isn't an id.
        $this->assertNull($el->addAttribute('id', true)->getId());
        $this->assertNull($el->addAttribute('id', null)->getId());
        $this->assertNull($el->removeAttribute('id')->getId());
    }//end testIdHelpers()

    /**
     * Tests adding classes in every shape.
     *
     * @return void
     */
    public function testAddClass(): void
    {
        $el = Domo::createElement('div');

        $this->assertSame($el, $el->addClass('a'));

        $el->addClass('b c')
            ->addClass(['d', 'e f'])
            ->addClass('a')
            ->addClass('')
            ->addClass([]);

        $this->assertSame('<div class="a b c d e f"></div>', $el->render());
    }//end testAddClass()

    /**
     * Tests that an element only asked about classes doesn't grow a class attribute.
     *
     * @return void
     */
    public function testEmptyClassListDoesNotRender(): void
    {
        $el = Domo::createElement('div');

        $this->assertFalse($el->hasClass('a'));

        $el->addClass('')->removeClass('a');

        $this->assertFalse($el->hasAttribute('class'));
        $this->assertSame('<div></div>', $el->render());
    }//end testEmptyClassListDoesNotRender()

    /**
     * Tests removing classes in every shape.
     *
     * @return void
     */
    public function testRemoveClass(): void
    {
        $el = Domo::createElement('div')->addClass('a b c d e');

        $this->assertSame($el, $el->removeClass('a'));

        $el->removeClass('b c')
            ->removeClass(['d', 'nope'])
            ->removeClass('');

        $this->assertSame('<div class="e"></div>', $el->render());
        $this->assertSame('<div></div>', $el->removeClass('e')->render());
    }//end testRemoveClass()

    /**
     * Tests asking after classes.
     *
     * @return void
     */
    public function testHasClass(): void
    {
        $el = Domo::createElement('div')->addClass('a b');

        $this->assertTrue($el->hasClass('a'));
        $this->assertTrue($el->hasClass('b a'));
        $this->assertFalse($el->hasClass('A'));
        $this->assertFalse($el->hasClass('c'));
        $this->assertFalse($el->hasClass('a c'));
        $this->assertFalse($el->hasClass(''));
    }//end testHasClass()

    /**
     * Tests that the class helpers work with classes set through `addAttribute`.
     *
     * @return void
     */
    public function testClassHelpersBuildOnAddAttribute(): void
    {
        $el = Domo::createElement('div')->addAttribute('class', 'a b');

        $el->addClass('c')->removeClass('a');

        $this->assertTrue($el->hasClass('b c'));
        $this->assertSame('<div class="b c"></div>', $el->render());
    }//end testClassHelpersBuildOnAddAttribute()

    /**
     * Tests that class values which can't be stored are refused.
     *
     * @return void
     */
    public function testRefusesInvalidClasses(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('div')->addClass(['a', null]);
    }//end testRefusesInvalidClasses()

    /**
     * Tests data attributes.
     *
     * @return void
     */
    public function testSetData(): void
    {
        $el = Domo::createElement('div');

        $this->assertSame($el, $el->setData('user-id', 5));

        $el->setData('Ratio', 0.5)
            ->setData('json', '{"a":"b"}')
            ->setData('flag', true)
            ->setData('off', false)
            ->setData('none', null)
            ->setData('empty', '');

        $this->assertSame(
            '<div data-user-id="5" data-ratio="0.5" data-json="{&quot;a&quot;:&quot;b&quot;}" data-flag data-empty=""></div>',
            $el->render()
        );
        $this->assertSame('6', $el->setData('user-id', 6)->getAttribute('data-user-id')->getValue());
    }//end testSetData()

    /**
     * Tests that a data name can't smuggle in more markup.
     *
     * @return void
     */
    public function testRefusesInvalidDataNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('div')->setData('x onclick=alert(1)', 'y');
    }//end testRefusesInvalidDataNames()

    /**
     * Tests ARIA attributes, whose booleans are spelled out.
     *
     * @return void
     */
    public function testSetAria(): void
    {
        $el = Domo::createElement('button');

        $this->assertSame($el, $el->setAria('label', 'Close "it"'));

        $el->setAria('expanded', false)
            ->setAria('pressed', true)
            ->setAria('level', 2)
            ->setAria('valuenow', 0.5)
            ->setAria('describedby', null)
            ->setAria('Hidden', 'true');

        $this->assertSame(
            '<button aria-label="Close &quot;it&quot;" aria-expanded="false" aria-pressed="true" aria-level="2" aria-valuenow="0.5" aria-hidden="true"></button>',
            $el->render()
        );
    }//end testSetAria()

    /**
     * Tests that an ARIA name can't smuggle in more markup.
     *
     * @return void
     */
    public function testRefusesInvalidAriaNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('div')->setAria('label="x" onclick', 'y');
    }//end testRefusesInvalidAriaNames()

    /**
     * Tests that two elements never share attribute objects.
     *
     * @return void
     */
    public function testElementsDoNotShareAttributes(): void
    {
        $one = Domo::createElement('div')->addClass('a');
        $two = Domo::createElement('div')->addClass('b');

        $this->assertSame('<div class="a"></div>', $one->render());
        $this->assertSame('<div class="b"></div>', $two->render());
    }//end testElementsDoNotShareAttributes()

    /**
     * Tests that rendering has no side effects.
     *
     * @return void
     */
    public function testRenderingTwiceGivesTheSameResult(): void
    {
        $el = Domo::createElement('div')->setId('a')->addClass('b c');

        $this->assertSame($el->render(), $el->render());
        $this->assertSame(' id="a" class="b c"', $el->renderAttrs());
    }//end testRenderingTwiceGivesTheSameResult()
}//end class
