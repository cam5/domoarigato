<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Div;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Doctype;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Cam5\Domoarigato\Domo::class)]
#[CoversClass(\Cam5\Domoarigato\Factories\ElementFactory::class)]
final class DomoTest extends TestCase
{
    public function testCreatesADivByName(): void
    {
        $this->assertInstanceOf(
            Div::class,
            Domo::createElement('div')
        );
    }

    public function testCreatesGenericElementByName(): void
    {
        $this->assertInstanceOf(
            GenericElement::class,
            Domo::createElement('test-element')
        );
    }

    public function testCreatesElementsConformingToACommonInterface(): void
    {
        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('any-old-thing')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('input')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('div')
        );
    }

    public function testCreatesElementsWithAttributes(): void
    {
        $el = Domo::createElement('a', ['href' => '/?a=1&b=2', 'class' => ['x', 'y'], 'download' => true, 'title' => null]);

        $this->assertSame('<a href="/?a=1&amp;b=2" class="x y" download></a>', $el->render());
    }

    public function testCreatesElementsWithContent(): void
    {
        $this->assertSame('<div>1 &lt; 2</div>', Domo::createElement('div', [], '1 < 2')->render());
        $this->assertSame('<div>0</div>', Domo::createElement('div', [], '0')->render());
        $this->assertSame('<div>0</div>', Domo::createElement('div', [], 0)->render());
        $this->assertSame('<div>1.5</div>', Domo::createElement('div', [], 1.5)->render());
        $this->assertSame('<div></div>', Domo::createElement('div', [], '')->render());
        $this->assertSame('<div></div>', Domo::createElement('div', [], null)->render());
        $this->assertSame('<div></div>', Domo::createElement('div', [], [])->render());
        $this->assertSame(
            '<div><div></div></div>',
            Domo::createElement('div', [], Domo::createElement('div'))->render()
        );
    }

    public function testCreatesWholeTrees(): void
    {
        $nav = Domo::createElement('nav', ['class' => 'menu'], [
            Domo::createElement('a', ['href' => '/'], 'Home'),
            ' | ',
            'second' => Domo::createElement('a', ['href' => '/about'], ['About ', Domo::createElement('b', [], 'us')]),
        ]);

        $this->assertSame(
            '<nav class="menu"><a href="/">Home</a> | <a href="/about">About <b>us</b></a></nav>',
            $nav->render()
        );
    }

    public function testNumericAttributeKeysAreStillNames(): void
    {
        $this->assertSame('<div 0="a" 1="b"></div>', Domo::createElement('div', ['a', 'b'])->render());
    }

    public function testRefusesInvalidAttributesWhenCreating(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('div', ['not valid' => 'x']);
    }

    public function testRefusesInvalidContentWhenCreating(): void
    {
        $this->expectException(\TypeError::class);

        Domo::createElement('div', [], [true]);
    }

    public function testSelfEnclosingElementsCannotBeGivenContent(): void
    {
        // Having nothing to put inside is fine.
        $this->assertSame('<input type="text" />', Domo::createElement('input', ['type' => 'text'], null)->render());
        $this->assertSame('<input />', Domo::createElement('input', [], [])->render());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A "input" element cannot have anything inside of it.');

        Domo::createElement('input', [], 'text');
    }

    public function testEvenEmptyTextIsContentToASelfEnclosingElement(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('input', [], '');
    }

    public function testNodeHelpers(): void
    {
        $this->assertInstanceOf(Text::class, Domo::text('a'));
        $this->assertSame('a &amp; b', Domo::text('a & b')->render());

        $this->assertInstanceOf(RawHtml::class, Domo::raw('a'));
        $this->assertSame('<b>a</b>', Domo::raw('<b>a</b>')->render());

        $this->assertInstanceOf(Comment::class, Domo::comment('a'));
        $this->assertSame('<!-- a -->', Domo::comment(' a ')->render());

        $this->assertInstanceOf(Doctype::class, Domo::doctype());
        $this->assertSame('<!DOCTYPE html>', Domo::doctype()->render());

        $this->assertInstanceOf(Fragment::class, Domo::fragment());
        $this->assertSame('', Domo::fragment()->render());
        $this->assertSame('a&lt;<div></div>1', Domo::fragment('a<', Domo::createElement('div'), 1)->render());
    }

    public function testCommentHelperRefusesUnsafeText(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Domo::comment('-->');
    }

    public function testBuildsAWholeDocument(): void
    {
        $document = Domo::fragment(
            Domo::doctype(),
            Domo::createElement('html', ['lang' => 'en'], [
                Domo::createElement('head', [], Domo::createElement('title', [], 'Tom & Jerry')),
                Domo::createElement('body', [], Domo::createElement('p', [], 'Hello')),
            ])
        );

        $this->assertSame(
            '<!DOCTYPE html><html lang="en"><head><title>Tom &amp; Jerry</title></head><body><p>Hello</p></body></html>',
            (string) $document
        );
    }
}
