<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\AbstractElement;
use Cam5\Domoarigato\Elements\Div;
use Cam5\Domoarigato\Elements\EnclosingElement;
use Cam5\Domoarigato\Elements\Input;
use Cam5\Domoarigato\Elements\SelfEnclosingElement;
use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\ParentNodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;
use Cam5\Domoarigato\Nodes\Traits\HasChildren;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests what can go inside of elements and fragments, and how it comes back out.
 */
#[CoversClass(EnclosingElement::class)]
#[CoversClass(SelfEnclosingElement::class)]
#[CoversClass(AbstractElement::class)]
#[CoversClass(Fragment::class)]
#[CoversTrait(HasChildren::class)]
final class ElementChildrenTest extends TestCase
{
    /**
     * Tests the README's opening example, `setText` and all.
     *
     * @return void
     */
    public function testReadmeExample(): void
    {
        $div = Domo::createElement('div');

        $div->setId('foo')
            ->addClass('bar')
            ->setText('baz');

        $this->assertSame('<div id="foo" class="bar">baz</div>', $div->render());
    }//end testReadmeExample()

    /**
     * Tests an element that has never been given anything.
     *
     * @return void
     */
    public function testStartsOutEmpty(): void
    {
        $div = new Div();

        $this->assertInstanceOf(ParentNodeInterface::class, $div);
        $this->assertSame([], $div->getChildren());
        $this->assertFalse($div->hasChildren());
        $this->assertSame('', $div->getTextContent());
        $this->assertSame('', $div->getInnerHtml());
        $this->assertSame('<div></div>', $div->render());
    }//end testStartsOutEmpty()

    /**
     * Text content, and how it renders inside of a div.
     *
     * @return array
     */
    public static function textContent(): array
    {
        return [
            'plain'            => ['Hello World', '<div>Hello World</div>'],
            'the string "0"'   => ['0', '<div>0</div>'],
            'integer'          => [42, '<div>42</div>'],
            'integer zero'     => [0, '<div>0</div>'],
            'float'            => [1.5, '<div>1.5</div>'],
            'whitespace only'  => ['   ', '<div>   </div>'],
            'markup'           => ['<script>alert(1)</script>', '<div>&lt;script&gt;alert(1)&lt;/script&gt;</div>'],
            'closing tag'      => ['</div><div>', '<div>&lt;/div&gt;&lt;div&gt;</div>'],
            'ampersand'        => ['a & b', '<div>a &amp; b</div>'],
            'quotes'           => ['"a" \'b\'', '<div>"a" \'b\'</div>'],
            'multibyte'        => ['日本語', '<div>日本語</div>'],
            'newlines'         => ["a\nb", "<div>a\nb</div>"],
        ];
    }//end textContent()

    /**
     * Tests that text content is escaped when rendered, and unescaped when read back.
     *
     * @param mixed  $text     The text as given.
     * @param string $expected The rendered element.
     *
     * @return void
     */
    #[DataProvider('textContent')]
    public function testTextContentIsEscaped(mixed $text, string $expected): void
    {
        $div = new Div();

        $this->assertSame($div, $div->setTextContent($text));
        $this->assertSame((string) $text, $div->getTextContent());
        $this->assertSame($expected, $div->render());
        $this->assertCount(1, $div->getChildren());
        $this->assertInstanceOf(Text::class, $div->getChildren()[0]);

        // The alias and plain appending do the very same thing.
        $this->assertSame($expected, (new Div())->setText($text)->render());
        $this->assertSame($expected, (new Div())->appendChild($text)->render());
    }//end testTextContentIsEscaped()

    /**
     * Tests that setting text replaces whatever was there, elements included.
     *
     * @return void
     */
    public function testSettingTextReplacesEverything(): void
    {
        $div = new Div();

        $div->appendChild(new Div())->appendChild('text')->setTextContent('new');

        $this->assertSame('<div>new</div>', $div->render());
        $this->assertCount(1, $div->getChildren());

        $div->setTextContent('');

        $this->assertSame('<div></div>', $div->render());
        $this->assertFalse($div->hasChildren());
    }//end testSettingTextReplacesEverything()

    /**
     * Tests nesting elements, in order.
     *
     * @return void
     */
    public function testNestsElements(): void
    {
        $list = Domo::createElement('ul');

        $this->assertSame($list, $list->appendChild(Domo::createElement('li')->setText('two')));

        $list->appendChild(Domo::createElement('li')->setText('three'))
            ->prependChild(Domo::createElement('li')->setText('one'));

        $this->assertSame('<ul><li>one</li><li>two</li><li>three</li></ul>', $list->render());
        $this->assertSame('<li>one</li><li>two</li><li>three</li>', $list->getInnerHtml());
        $this->assertSame('onetwothree', $list->getTextContent());
        $this->assertCount(3, $list->getChildren());
        $this->assertTrue($list->hasChildren());
    }//end testNestsElements()

    /**
     * Tests mixing text, elements and self-enclosing elements.
     *
     * @return void
     */
    public function testMixedContent(): void
    {
        $p = Domo::createElement('p');

        $p->append(
            'Fish & ',
            Domo::createElement('em')->setText('chips'),
            Domo::createElement('input', ['type' => 'hidden']),
            ' < ',
            3,
            1.5
        );

        $this->assertSame('<p>Fish &amp; <em>chips</em><input type="hidden" /> &lt; 31.5</p>', $p->render());
        $this->assertSame('Fish & chips < 31.5', $p->getTextContent());
        $this->assertCount(6, $p->getChildren());
    }//end testMixedContent()

    /**
     * Tests the edge cases of the variadic `append`.
     *
     * @return void
     */
    public function testAppend(): void
    {
        $div = new Div();

        $this->assertSame($div, $div->append());
        $this->assertFalse($div->hasChildren());

        $div->append('a')->append(...['x' => 'b', 'y' => 'c'])->append('', '0');

        $this->assertSame('<div>abc0</div>', $div->render());
        $this->assertCount(5, $div->getChildren());
    }//end testAppend()

    /**
     * Tests that when one node of several is refused, none of them are added.
     *
     * @return void
     */
    public function testAppendIsAllOrNothing(): void
    {
        $div = (new Div())->setText('kept');

        try {
            $div->append('a', $div, 'b');
            $this->fail('An element was placed inside of itself.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('<div>kept</div>', $div->render());
        }
    }//end testAppendIsAllOrNothing()

    /**
     * Tests prepending onto nothing, and prepending text.
     *
     * @return void
     */
    public function testPrepend(): void
    {
        $div = new Div();

        $this->assertSame($div, $div->prependChild('b'));

        $div->prependChild('<a>');

        $this->assertSame('<div>&lt;a&gt;b</div>', $div->render());
        $this->assertSame([0, 1], array_keys($div->getChildren()));
    }//end testPrepend()

    /**
     * Tests deep nesting.
     *
     * @return void
     */
    public function testDeepNesting(): void
    {
        $root    = new Div();
        $current = $root;

        for ($i = 0; $i < 200; $i++) {
            $child = new Div();
            $current->appendChild($child);
            $current = $child;
        }

        $current->setText('bottom');

        $this->assertSame(str_repeat('<div>', 201).'bottom'.str_repeat('</div>', 201), $root->render());
        $this->assertSame('bottom', $root->getTextContent());
        $this->assertTrue($root->contains($current));
        $this->assertFalse($current->contains($root));
    }//end testDeepNesting()

    /**
     * Tests that raw markup goes out untouched, where text would be escaped.
     *
     * @return void
     */
    public function testInnerHtml(): void
    {
        $div = (new Div())->setText('old');

        $this->assertSame($div, $div->setInnerHtml('<b>bold</b> &amp; <i>italic</i>'));
        $this->assertSame('<div><b>bold</b> &amp; <i>italic</i></div>', $div->render());
        $this->assertSame('<b>bold</b> &amp; <i>italic</i>', $div->getInnerHtml());
        $this->assertSame('bold & italic', $div->getTextContent());
        $this->assertCount(1, $div->getChildren());
        $this->assertInstanceOf(RawHtml::class, $div->getChildren()[0]);

        $div->setInnerHtml('');

        $this->assertFalse($div->hasChildren());
        $this->assertSame('<div></div>', $div->render());
    }//end testInnerHtml()

    /**
     * Tests that comments render, but aren't text.
     *
     * @return void
     */
    public function testCommentsInsideElements(): void
    {
        $div = (new Div())->append('a', new Comment(' note '), 'b');

        $this->assertSame('<div>a<!-- note -->b</div>', $div->render());
        $this->assertSame('ab', $div->getTextContent());
    }//end testCommentsInsideElements()

    /**
     * Tests emptying an element.
     *
     * @return void
     */
    public function testRemoveChildren(): void
    {
        $div = (new Div())->append('a', new Div());

        $this->assertSame($div, $div->removeChildren());
        $this->assertSame([], $div->getChildren());
        $this->assertSame('<div></div>', $div->render());
        $this->assertSame($div, $div->removeChildren());
    }//end testRemoveChildren()

    /**
     * Tests that an element can't go inside of itself, directly or through its descendants.
     *
     * @return void
     */
    public function testRefusesCycles(): void
    {
        $a = new Div();
        $b = new Div();
        $c = new Div();

        $a->appendChild($b);
        $b->appendChild($c);

        $attempts = [
            'itself'                => fn () => $a->appendChild($a),
            'its parent'            => fn () => $b->appendChild($a),
            'its grandparent'       => fn () => $c->appendChild($a),
            'its parent, prepended' => fn () => $c->prependChild($b),
            'a fragment holding it' => fn () => $c->appendChild(new Fragment('x', $a)),
        ];

        foreach ($attempts as $what => $attempt) {
            try {
                $attempt();
                $this->fail('An element was placed inside of '.$what.'.');
            } catch (\InvalidArgumentException $e) {
                $this->assertSame('A node cannot be placed inside of itself.', $e->getMessage());
            }
        }

        $this->assertSame('<div><div><div></div></div></div>', $a->render());
    }//end testRefusesCycles()

    /**
     * Tests that the same node may appear in more than one place: it simply renders in each.
     *
     * @return void
     */
    public function testANodeCanBeUsedTwice(): void
    {
        $icon = Domo::createElement('i')->addClass('icon');
        $div  = (new Div())->append($icon, ' and ', $icon);

        $this->assertSame('<div><i class="icon"></i> and <i class="icon"></i></div>', $div->render());

        // It is one object, so a change shows up everywhere.
        $icon->addClass('big');

        $this->assertSame('<div><i class="icon big"></i> and <i class="icon big"></i></div>', $div->render());
    }//end testANodeCanBeUsedTwice()

    /**
     * Tests looking for nodes inside of an element.
     *
     * @return void
     */
    public function testContains(): void
    {
        $text  = new Text('deep');
        $inner = (new Div())->appendChild($text);
        $outer = (new Div())->appendChild(new Fragment($inner));
        $other = new Div();

        $this->assertTrue($outer->contains($outer));
        $this->assertTrue($outer->contains($inner));
        $this->assertTrue($outer->contains($text));
        $this->assertTrue($inner->contains($text));
        $this->assertFalse($inner->contains($outer));
        $this->assertFalse($outer->contains($other));
        $this->assertFalse($outer->contains(new Text('deep')));
    }//end testContains()

    /**
     * Tests that a clone is a deep copy: nothing done to it reaches the original.
     *
     * @return void
     */
    public function testCloningIsDeep(): void
    {
        $original = Domo::createElement('div', ['id' => 'a', 'class' => 'one'], [
            Domo::createElement('span', ['class' => 'inner'], 'text'),
        ]);

        $copy = clone $original;

        $copy->setId('b')->addClass('two');
        $copy->getChildren()[0]->addClass('changed')->setText('other');
        $copy->appendChild('more');

        $this->assertSame('<div id="a" class="one"><span class="inner">text</span></div>', $original->render());
        $this->assertSame(
            '<div id="b" class="one two"><span class="inner changed">other</span>more</div>',
            $copy->render()
        );
    }//end testCloningIsDeep()

    /**
     * Tests that self-enclosing elements clone their attributes as well.
     *
     * @return void
     */
    public function testCloningSelfEnclosingElements(): void
    {
        $original = (new Input())->addClass('a');
        $copy     = clone $original;

        $copy->addClass('b');

        $this->assertSame('<input class="a" />', $original->render());
        $this->assertSame('<input class="a b" />', $copy->render());
    }//end testCloningSelfEnclosingElements()

    /**
     * Tests that self-enclosing elements have nothing inside, and no way to put anything there.
     *
     * @return void
     */
    public function testSelfEnclosingElementsHoldNothing(): void
    {
        $input = new Input();

        $this->assertNotInstanceOf(ParentNodeInterface::class, $input);
        $this->assertSame('', $input->getTextContent());
        $this->assertFalse(method_exists($input, 'appendChild'));
        $this->assertFalse(method_exists($input, 'setTextContent'));
        $this->assertFalse(method_exists($input, 'setInnerHtml'));
    }//end testSelfEnclosingElementsHoldNothing()

    /**
     * Tests that elements can be echoed.
     *
     * @return void
     */
    public function testElementsAreStringable(): void
    {
        $div = (new Div())->setText('a');

        $this->assertInstanceOf(\Stringable::class, $div);
        $this->assertSame('<div>a</div>', (string) $div);
        $this->assertSame('<input />', (string) new Input());
        $this->assertSame('x<div>a</div>y', "x{$div}y");
    }//end testElementsAreStringable()

    /**
     * Tests grouping nodes without a wrapper.
     *
     * @return void
     */
    public function testFragments(): void
    {
        $empty = new Fragment();

        $this->assertInstanceOf(ParentNodeInterface::class, $empty);
        $this->assertSame('', $empty->render());
        $this->assertSame('', (string) $empty);
        $this->assertFalse($empty->hasChildren());

        $fragment = new Fragment('a < b', new Div(), 5);

        $this->assertSame('a &lt; b<div></div>5', $fragment->render());
        $this->assertSame('a < b5', $fragment->getTextContent());
        $this->assertCount(3, $fragment->getChildren());

        $fragment->prependChild(new Comment('first'))->appendChild('last');

        $this->assertSame('<!--first-->a &lt; b<div></div>5last', (string) $fragment);

        // Inside an element, a fragment adds its children and nothing more.
        $this->assertSame('<div>a<b>c</b></div>', (new Div())->append('a', new Fragment(Domo::raw('<b>c</b>')))->render());
    }//end testFragments()

    /**
     * Tests the rest of what fragments share with elements.
     *
     * @return void
     */
    public function testFragmentsCloneAndRefuseCycles(): void
    {
        $inner    = (new Div())->setText('a');
        $fragment = new Fragment($inner);
        $copy     = clone $fragment;

        $copy->getChildren()[0]->setText('b');

        $this->assertSame('<div>a</div>', $fragment->render());
        $this->assertSame('<div>b</div>', $copy->render());
        $this->assertSame('x', $copy->setText('x')->render());
        $this->assertSame('<i></i>', $copy->setInnerHtml('<i></i>')->render());

        $this->expectException(\InvalidArgumentException::class);

        $inner->appendChild($fragment);
    }//end testFragmentsCloneAndRefuseCycles()

    /**
     * Tests that a fragment can't be built around itself.
     *
     * @return void
     */
    public function testFragmentRefusesItself(): void
    {
        $fragment = new Fragment();

        $this->expectException(\InvalidArgumentException::class);

        $fragment->appendChild($fragment);
    }//end testFragmentRefusesItself()
}//end class
