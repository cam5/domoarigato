<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Elements\EnclosingElement;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Elements\ObjectElement;
use Cam5\Domoarigato\Elements\RawTextElement;
use Cam5\Domoarigato\Elements\SelfEnclosingElement;
use Cam5\Domoarigato\Elements\TextOnlyElement;
use Cam5\Domoarigato\Elements\Traits\BaseElement;
use Cam5\Domoarigato\Elements\VarElement;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Factories\ElementFactory;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the element enum, and every element class, against a snapshot of the HTML Living
 * Standard's element index.
 *
 * Which elements are void, raw text and so on is written out here by hand from the "HTML syntax"
 * section of the spec, as a second opinion on the classes.
 */
#[CoversClass(Elements::class)]
#[CoversClass(ElementFactory::class)]
#[CoversClass(EnclosingElement::class)]
#[CoversClass(SelfEnclosingElement::class)]
#[CoversClass(RawTextElement::class)]
#[CoversClass(TextOnlyElement::class)]
#[CoversTrait(BaseElement::class)]
final class ElementsTest extends TestCase
{

    /**
     * The void elements: a start tag only, and nothing inside.
     *
     * @see https://html.spec.whatwg.org/multipage/syntax.html#void-elements
     *
     * @var string[]
     */
    const VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr',
    ];

    /**
     * The raw text elements: their content is never escaped.
     *
     * @var string[]
     */
    const RAW_TEXT_ELEMENTS = ['script', 'style'];

    /**
     * The escapable raw text elements: text only, escaped as usual.
     *
     * @var string[]
     */
    const TEXT_ONLY_ELEMENTS = ['textarea', 'title'];

    /**
     * Elements whose names can't be class names in PHP.
     *
     * @var array
     */
    const RENAMED_CLASSES = [
        'var'    => VarElement::class,
        'object' => ObjectElement::class,
    ];

    /**
     * Reads the element names out of the snapshot of the spec.
     *
     * @return string[]
     */
    private static function specElements(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../fixtures/whatwg-elements.json'), true)['elements'];
    }//end specElements()

    /**
     * Works out which base class an element should have.
     *
     * @param string $name The tag name.
     *
     * @return string
     */
    private static function expectedBase(string $name): string
    {
        if (true === in_array($name, self::VOID_ELEMENTS, true)) {
            return SelfEnclosingElement::class;
        }

        if (true === in_array($name, self::RAW_TEXT_ELEMENTS, true)) {
            return RawTextElement::class;
        }

        if (true === in_array($name, self::TEXT_ONLY_ELEMENTS, true)) {
            return TextOnlyElement::class;
        }

        return EnclosingElement::class;
    }//end expectedBase()

    /**
     * Every element in the spec, with the base class we expect for it.
     *
     * @return array
     */
    public static function elements(): array
    {
        $data = [];

        foreach (self::specElements() as $name) {
            $data[$name] = [$name, self::expectedBase($name)];
        }

        return $data;
    }//end elements()

    /**
     * Only the elements of one kind.
     *
     * @param string $base The base class to filter by.
     *
     * @return array
     */
    private static function elementsOfKind(string $base): array
    {
        return array_filter(self::elements(), fn ($element) => $base === $element[1]);
    }//end elementsOfKind()

    /**
     * The elements that can hold other elements.
     *
     * @return array
     */
    public static function ordinaryElements(): array
    {
        return self::elementsOfKind(EnclosingElement::class);
    }//end ordinaryElements()

    /**
     * The elements that can't hold anything.
     *
     * @return array
     */
    public static function voidElements(): array
    {
        return self::elementsOfKind(SelfEnclosingElement::class);
    }//end voidElements()

    /**
     * Tests that the snapshot is the size we think it is, and the hand-written lists are all in it.
     *
     * @return void
     */
    public function testSnapshotIsComplete(): void
    {
        $names = self::specElements();

        // 113 HTML elements, plus the <math> and <svg> that embed other vocabularies.
        $this->assertCount(115, $names);
        $this->assertCount(115, array_unique($names));
        $this->assertCount(13, self::voidElements());
        $this->assertCount(98, self::ordinaryElements());

        foreach ([self::VOID_ELEMENTS, self::RAW_TEXT_ELEMENTS, self::TEXT_ONLY_ELEMENTS] as $list) {
            $this->assertSame([], array_diff($list, $names));
        }
    }//end testSnapshotIsComplete()

    /**
     * Tests that the enum has every element in the spec, and nothing else.
     *
     * @return void
     */
    public function testEnumMatchesTheSpecExactly(): void
    {
        $expected = self::specElements();
        $actual   = array_keys(Elements::all());

        sort($expected);
        sort($actual);

        $this->assertSame([], array_values(array_diff($expected, $actual)), 'Missing from the enum.');
        $this->assertSame([], array_values(array_diff($actual, $expected)), 'Not in the spec.');
        $this->assertSame($expected, $actual);
    }//end testEnumMatchesTheSpecExactly()

    /**
     * Tests that there's a constant for every key, a key for every constant, and a class of its own for each.
     *
     * @return void
     */
    public function testConstantsKeysAndClassesLineUp(): void
    {
        $constants = (new \ReflectionClass(Elements::class))->getConstants();

        $this->assertEqualsCanonicalizing(array_keys(Elements::all()), array_values($constants));

        foreach ($constants as $constant => $name) {
            $this->assertSame(strtoupper($name), $constant);
        }

        $classes = array_values(Elements::all());

        $this->assertSame(count($classes), count(array_unique($classes)));
    }//end testConstantsKeysAndClassesLineUp()

    /**
     * Tests that there is no element class on disk that the enum doesn't know about.
     *
     * @return void
     */
    public function testEveryElementClassIsRegistered(): void
    {
        $registered = array_flip(Elements::all());

        foreach (glob(dirname(__DIR__, 2).'/src/Elements/*.php') as $file) {
            $class      = 'Cam5\\Domoarigato\\Elements\\'.basename($file, '.php');
            $reflection = new \ReflectionClass($class);

            if (true === $reflection->isInterface()
                || true === $reflection->isAbstract()
                || GenericElement::class === $class
            ) {
                continue;
            }

            $this->assertArrayHasKey($class, $registered, $class.' is not in the enum.');
            $this->assertSame($registered[$class], $reflection->getConstant('TAG_NAME'));
        }
    }//end testEveryElementClassIsRegistered()

    /**
     * Tests each element's class: its name, its lineage and its tag.
     *
     * @param string $name The tag name.
     * @param string $base The base class we expect.
     *
     * @return void
     */
    #[DataProvider('elements')]
    public function testElementHasAClassOfTheRightKind(string $name, string $base): void
    {
        $this->assertTrue(Elements::contains($name));
        $this->assertTrue(Elements::contains(strtoupper($name)));

        $class = Elements::get($name);

        $this->assertSame(
            (self::RENAMED_CLASSES[$name] ?? 'Cam5\\Domoarigato\\Elements\\'.ucfirst($name)),
            $class
        );
        $this->assertSame($base, get_parent_class($class));
        $this->assertSame($name, $class::TAG_NAME);

        $el = new $class();

        $this->assertInstanceOf(ElementInterface::class, $el);
        $this->assertSame($name, $el->getTagName());
        $this->assertSame((SelfEnclosingElement::class !== $base), ($el instanceof ParentNodeInterface));
    }//end testElementHasAClassOfTheRightKind()

    /**
     * Tests that the factory hands out each element's own class, whatever case it's asked in.
     *
     * @param string $name The tag name.
     *
     * @return void
     */
    #[DataProvider('elements')]
    public function testFactoryCreatesTheElement(string $name): void
    {
        $class = Elements::get($name);

        foreach ([$name, strtoupper($name), ucfirst($name)] as $spelling) {
            $el = Domo::createElement($spelling);

            $this->assertSame($class, get_class($el));
            $this->assertNotInstanceOf(GenericElement::class, $el);
            $this->assertSame($name, $el->getTagName());
        }

        $this->assertNotSame(Domo::createElement($name), Domo::createElement($name));
    }//end testFactoryCreatesTheElement()

    /**
     * Tests how each element renders when empty, and with attributes.
     *
     * @param string $name The tag name.
     * @param string $base The base class we expect.
     *
     * @return void
     */
    #[DataProvider('elements')]
    public function testElementRenders(string $name, string $base): void
    {
        $format = (SelfEnclosingElement::class === $base) ? '<'.$name.'%s />' : '<'.$name.'%s></'.$name.'>';

        $el = Domo::createElement($name);

        $this->assertSame(sprintf($format, ''), $el->render());
        $this->assertSame(sprintf($format, ''), (string) $el);
        $this->assertSame('', $el->getTextContent());

        $el->setId('a')
            ->addClass('b c')
            ->addAttribute('hidden', true)
            ->addAttribute('title', '"q" & <a>')
            ->setData('n', 1);

        $this->assertSame(
            sprintf($format, ' id="a" class="b c" hidden title="&quot;q&quot; &amp; &lt;a&gt;" data-n="1"'),
            $el->render()
        );

        $copy = clone $el;
        $copy->removeAttribute('title')->removeClass('b');

        $this->assertSame(sprintf($format, ' id="a" class="c" hidden data-n="1"'), $copy->render());
        $this->assertTrue($el->hasClass('b'));
    }//end testElementRenders()

    /**
     * Tests that ordinary elements hold escaped text and other elements.
     *
     * @param string $name The tag name.
     *
     * @return void
     */
    #[DataProvider('ordinaryElements')]
    public function testOrdinaryElementHoldsContent(string $name): void
    {
        $el = Domo::createElement($name)->setText('a < b & c');

        $this->assertSame('<'.$name.'>a &lt; b &amp; c</'.$name.'>', $el->render());
        $this->assertSame('a < b & c', $el->getTextContent());

        $el->appendChild(Domo::createElement('span', [], '!'))->prependChild(Domo::comment('c'));

        $this->assertSame('<'.$name.'><!--c-->a &lt; b &amp; c<span>!</span></'.$name.'>', $el->render());

        $el->setInnerHtml('<b>raw</b>');

        $this->assertSame('<'.$name.'><b>raw</b></'.$name.'>', $el->render());
        $this->assertSame(
            '<'.$name.' lang="en">x</'.$name.'>',
            Domo::createElement($name, ['lang' => 'en'], 'x')->render()
        );
    }//end testOrdinaryElementHoldsContent()

    /**
     * Tests that void elements never render a closing tag, and can't be given content.
     *
     * @param string $name The tag name.
     *
     * @return void
     */
    #[DataProvider('voidElements')]
    public function testVoidElementHoldsNothing(string $name): void
    {
        $el = Domo::createElement($name, ['id' => 'x']);

        $this->assertSame('<'.$name.' id="x" />', $el->render());
        $this->assertStringNotContainsString('</', $el->render());
        $this->assertFalse(method_exists($el, 'appendChild'));
        $this->assertFalse(method_exists($el, 'setText'));
        $this->assertFalse(method_exists($el, 'setInnerHtml'));

        // Inside another element, it sits between its siblings without swallowing them.
        $this->assertSame(
            '<div>a<'.$name.' id="x" />b</div>',
            Domo::createElement('div', [], ['a', $el, 'b'])->render()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A "'.$name.'" element cannot have anything inside of it.');

        Domo::createElement($name, [], 'content');
    }//end testVoidElementHoldsNothing()

    /**
     * Tests that elements which merely have an empty content model are not mistaken for void ones.
     *
     * @return void
     */
    public function testEmptyContentModelIsNotVoid(): void
    {
        foreach (['iframe', 'template', 'selectedcontent', 'canvas', 'video', 'audio', 'object', 'slot'] as $name) {
            $this->assertSame('<'.$name.'></'.$name.'>', Domo::createElement($name)->render());
        }

        // "textarea" and "title" need their closing tags most of all.
        $this->assertSame('<textarea></textarea>', Domo::createElement('textarea')->render());
        $this->assertSame('<title></title>', Domo::createElement('title')->render());
        $this->assertSame('<script></script>', Domo::createElement('script')->render());
    }//end testEmptyContentModelIsNotVoid()

    /**
     * Tests the elements whose names PHP wouldn't take as class names.
     *
     * @return void
     */
    public function testReservedWordElements(): void
    {
        $var    = Domo::createElement('var', [], 'x');
        $object = Domo::createElement('object', ['data' => 'movie.swf'], 'Fallback');

        $this->assertInstanceOf(VarElement::class, $var);
        $this->assertSame('<var>x</var>', $var->render());
        $this->assertInstanceOf(ObjectElement::class, $object);
        $this->assertSame('<object data="movie.swf">Fallback</object>', $object->render());
    }//end testReservedWordElements()

    /**
     * Tests that names outside the spec fall back to a generic element.
     *
     * @return void
     */
    public function testUnknownElementsAreGeneric(): void
    {
        $names = [
            // Custom elements, and near misses.
            'my-element', 'divv', 'h7', 'x-div',
            // Obsolete elements, which are no longer part of HTML.
            'center', 'font', 'marquee', 'blink', 'frame', 'frameset', 'acronym', 'big', 'tt', 'param',
            // The contents of an <svg>.
            'path', 'circle', 'g',
        ];

        foreach ($names as $name) {
            $this->assertFalse(Elements::contains($name), $name);

            $el = Domo::createElement($name);

            $this->assertInstanceOf(GenericElement::class, $el);
            $this->assertSame('<'.$name.'></'.$name.'>', $el->render());
        }
    }//end testUnknownElementsAreGeneric()

    /**
     * Tests the heading elements, which the spec's index lists on a single row.
     *
     * @return void
     */
    public function testAllSixHeadings(): void
    {
        for ($level = 1; $level <= 6; $level++) {
            $this->assertSame(
                '<h'.$level.'>Title</h'.$level.'>',
                Domo::createElement('h'.$level, [], 'Title')->render()
            );
        }
    }//end testAllSixHeadings()

    /**
     * Tests embedding an SVG, whose own elements are generic.
     *
     * @return void
     */
    public function testEmbedsSvgAndMath(): void
    {
        $svg = Domo::createElement('svg', ['viewBox' => '0 0 10 10', 'width' => 10], [
            Domo::createElement('circle', ['cx' => 5, 'cy' => 5, 'r' => 4.5]),
        ]);

        // Attribute names are lowercased; an HTML parser restores "viewBox" inside of an <svg>.
        $this->assertSame(
            '<svg viewbox="0 0 10 10" width="10"><circle cx="5" cy="5" r="4.5"></circle></svg>',
            $svg->render()
        );

        $math = Domo::createElement('math', [], Domo::createElement('mi', [], 'x'));

        $this->assertSame('<math><mi>x</mi></math>', $math->render());
    }//end testEmbedsSvgAndMath()
}//end class
