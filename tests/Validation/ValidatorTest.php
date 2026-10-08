<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\AbstractElement;
use Cam5\Domoarigato\Elements\EnclosingElement;
use Cam5\Domoarigato\Elements\SelfEnclosingElement;
use Cam5\Domoarigato\Enums\ContentModels;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Doctype;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;
use Cam5\Domoarigato\Validation\InvalidContentException;
use Cam5\Domoarigato\Validation\Traits\Validates;
use Cam5\Domoarigato\Validation\Validator;
use Cam5\Domoarigato\Validation\Violation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests checking trees of nodes against HTML's content models.
 */
#[CoversClass(Validator::class)]
#[CoversClass(Violation::class)]
#[CoversClass(InvalidContentException::class)]
#[CoversClass(AbstractElement::class)]
#[CoversClass(EnclosingElement::class)]
#[CoversClass(SelfEnclosingElement::class)]
#[CoversClass(Fragment::class)]
#[CoversClass(Text::class)]
#[CoversClass(RawHtml::class)]
#[CoversClass(Comment::class)]
#[CoversClass(Doctype::class)]
#[CoversTrait(Validates::class)]
final class ValidatorTest extends TestCase
{
    /**
     * Shorthand for building an element.
     *
     * @param string $name       The tag name.
     * @param mixed  $content    What to put inside.
     * @param array  $attributes The attributes to give it.
     *
     * @return NodeInterface
     */
    private static function el(string $name, mixed $content = null, array $attributes = []): NodeInterface
    {
        return Domo::createElement($name, $attributes, $content);
    }//end el()

    /**
     * Describes each violation in a tree as a line of text.
     *
     * @param NodeInterface $root The top of the tree.
     *
     * @return string[]
     */
    private static function problems(NodeInterface $root): array
    {
        return array_map('strval', Validator::validate($root));
    }//end problems()

    /**
     * Trees that follow the rules.
     *
     * @return array
     */
    public static function validTrees(): array
    {
        $e = fn (...$args) => self::el(...$args);

        return [
            'empty div'                   => [fn () => $e('div')],
            'text in a paragraph'         => [fn () => $e('p', 'Hello')],
            'phrasing in a paragraph'     => [fn () => $e('p', ['a ', $e('em', 'b'), $e('br'), $e('img')])],
            'flow in a div'               => [fn () => $e('div', [$e('p', 'x'), $e('ul', $e('li', 'y')), 'text'])],
            'list items'                  => [fn () => $e('ul', [$e('li', 'a'), $e('li', $e('p', 'b'))])],
            'ordered list and menu'       => [fn () => $e('div', [$e('ol', $e('li')), $e('menu', $e('li'))])],
            'whitespace between items'    => [fn () => $e('ul', ["\n  ", $e('li'), " \t\r\f\n", $e('li'), "\n"])],
            'comments anywhere'           => [fn () => $e('ul', [Domo::comment('a'), $e('li'), Domo::comment('b')])],
            'script and template in a ul' => [fn () => $e('ul', [$e('script', 'x()'), $e('template'), $e('li')])],
            'a void element alone'        => [fn () => $e('br')],
            'a lone text node'            => [fn () => Domo::text('x')],
            'a lone comment'              => [fn () => Domo::comment('x')],
            'a lone doctype'              => [fn () => Domo::doctype()],
            'raw html alone'              => [fn () => Domo::raw('<ul><p>unchecked</p></ul>')],
            'an empty fragment'           => [fn () => Domo::fragment()],
            'table'                       => [
                fn () => $e('table', [
                    $e('caption', 'c'),
                    $e('colgroup', $e('col')),
                    $e('thead', $e('tr', $e('th', 'h'))),
                    $e('tbody', $e('tr', [$e('td', $e('p', 'd')), $e('th')])),
                    $e('tfoot', $e('tr', $e('td'))),
                ]),
            ],
            'table with bare rows'        => [fn () => $e('table', $e('tr', $e('td', 'x')))],
            'description list'            => [fn () => $e('dl', [$e('dt', 'term'), $e('dd', $e('p', 'desc'))])],
            'description list with divs'  => [fn () => $e('dl', $e('div', [$e('dt', 't'), $e('dd', 'd'), $e('script')]))],
            'select'                      => [
                fn () => $e('select', [$e('option', 'a'), $e('optgroup', $e('option', 'b')), $e('hr')]),
            ],
            'datalist'                    => [fn () => $e('datalist', [$e('option', 'a'), 'or ', $e('em', 'b')])],
            'details'                     => [fn () => $e('details', [$e('summary', ['s', $e('h2', 'h')]), $e('p', 'x')])],
            'fieldset'                    => [fn () => $e('fieldset', [$e('legend', $e('h3', 'l')), $e('input')])],
            'figure'                      => [fn () => $e('figure', [$e('img'), $e('figcaption', $e('p', 'c'))])],
            'ruby'                        => [fn () => $e('ruby', ['漢', $e('rp', '('), $e('rt', 'kan'), $e('rp', ')')])],
            'picture'                     => [fn () => $e('picture', [$e('source'), $e('img')])],
            'hgroup'                      => [fn () => $e('hgroup', [$e('h1', 'a'), $e('p', 'b')])],
            'video'                       => [fn () => $e('div', $e('video', [$e('source'), $e('track'), $e('p', 'no video')]))],
            'map with areas'              => [fn () => $e('p', $e('map', [$e('area'), $e('img')]))],
            'whole document'              => [
                fn () => Domo::fragment(
                    Domo::doctype(),
                    $e('html', [
                        $e('head', [$e('title', 'T'), $e('meta'), $e('link'), $e('style', 'a{}'), $e('script'), $e('base')]),
                        $e('body', [$e('main', $e('h1', 'Hi')), $e('footer', 'bye')]),
                    ])
                ),
            ],
            'noscript in head'            => [fn () => $e('head', [$e('title', 't'), $e('noscript', [$e('link'), $e('style'), $e('meta')])])],
            'noscript in body'            => [fn () => $e('div', $e('noscript', $e('p', 'Enable JavaScript')))],
            'noscript in a paragraph'     => [fn () => $e('p', $e('noscript', $e('em', 'x')))],
            'link in body with itemprop'  => [fn () => $e('div', $e('link', null, ['itemprop' => 'url']))],
            'stylesheet link in body'     => [fn () => $e('div', $e('link', null, ['rel' => 'stylesheet']))],
            'meta in body with itemprop'  => [fn () => $e('span', $e('meta', null, ['itemprop' => 'name']))],
            'a wrapping a block in a div' => [fn () => $e('div', $e('a', $e('div', 'x')))],
            'nested transparent in flow'  => [fn () => $e('li', $e('ins', $e('a', $e('del', $e('p', 'x')))))],
            'a with no parent to go by'   => [fn () => $e('a', [$e('div'), $e('li'), 'text'])],
            'ins with no parent'          => [fn () => $e('ins', $e('ins', $e('div')))],
            'custom element in a p'       => [fn () => $e('p', $e('my-icon', 'x'))],
            'custom element in a div'     => [fn () => $e('div', $e('my-card', $e('p', 'x')))],
            'custom element, no parent'   => [fn () => $e('my-list', [$e('li'), $e('td')])],
            'unknown element in a div'    => [fn () => $e('div', $e('center', $e('p', 'x')))],
            'svg is left alone'           => [fn () => $e('p', $e('svg', [$e('circle'), $e('p', $e('div')), $e('li')]))],
            'math is left alone'          => [fn () => $e('p', $e('math', $e('mrow', $e('div'))))],
            'template is left alone'      => [fn () => $e('ul', $e('template', [$e('td'), 'text', $e('ul', $e('p'))]))],
            'raw html is left alone'      => [fn () => $e('ul', Domo::raw('<p>unchecked</p>'))],
            'fragment of list items'      => [fn () => $e('ul', Domo::fragment($e('li'), Domo::fragment($e('li'))))],
            'fragment of anything'        => [fn () => Domo::fragment($e('li'), 'text', $e('td'), $e('html'))],
            'title, textarea, rp'         => [fn () => $e('div', [$e('textarea', 'x'), $e('ruby', $e('rp', '('))])],
            'option with text'            => [fn () => $e('option', ['a', $e('em', 'b'), $e('div')])],
            'button with phrasing'        => [fn () => $e('button', ['Go ', $e('strong', 'now')])],
            'heading in a label legend'   => [fn () => $e('summary', [$e('hgroup', $e('h1')), $e('em')])],
            'empty iframe'                => [fn () => $e('iframe', [' ', Domo::comment('x')])],
            'object with fallback'        => [fn () => $e('div', $e('object', $e('p', 'fallback')))],
            'canvas slot in phrasing'     => [fn () => $e('p', [$e('canvas', $e('em')), $e('slot', 'x')])],
            'main, and area in a map'     => [fn () => $e('div', [$e('main'), $e('map', $e('area'))])],
        ];
    }//end validTrees()

    /**
     * Tests that valid trees have no violations, and render the same with or without validation.
     *
     * @param callable $build Builds the tree.
     *
     * @return void
     */
    #[DataProvider('validTrees')]
    public function testValidTreesPass(callable $build): void
    {
        $root = $build();

        $this->assertSame([], self::problems($root));
        $this->assertSame($root->render(), $root->render(true));
        $this->assertSame($root->render(), $root->render(false));

        Validator::assertValid($root);
    }//end testValidTreesPass()

    /**
     * Trees that break the rules, with every violation we expect, in order.
     *
     * @return array
     */
    public static function invalidTrees(): array
    {
        $e = fn (...$args) => self::el(...$args);

        $inUl    = 'Allowed there: <li>, script-supporting elements.';
        $inP     = 'Allowed there: phrasing content.';
        $nothing = 'Nothing is allowed there.';

        return [
            'paragraph in a list'         => [
                fn () => $e('ul', $e('p', 'x')),
                ['ul > p: <p> is not allowed directly inside of <ul>. '.$inUl],
            ],
            'text in a list'              => [
                fn () => $e('ul', ['loose', $e('li')]),
                ['ul > (text): Text is not allowed directly inside of <ul>. '.$inUl],
            ],
            'non-breaking space in a list' => [
                fn () => $e('ol', "\u{A0}"),
                ['ol > (text): Text is not allowed directly inside of <ol>. '.$inUl],
            ],
            'the text "0" in a list'      => [
                fn () => $e('ul', '0'),
                ['ul > (text): Text is not allowed directly inside of <ul>. '.$inUl],
            ],
            'div in a paragraph'          => [
                fn () => $e('p', $e('div')),
                ['p > div: <div> is not allowed directly inside of <p>. '.$inP],
            ],
            'paragraph in a paragraph'    => [
                fn () => $e('p', $e('p')),
                ['p > p: <p> is not allowed directly inside of <p>. '.$inP],
            ],
            'list in a span'              => [
                fn () => $e('span', $e('ul', $e('li'))),
                ['span > ul: <ul> is not allowed directly inside of <span>. '.$inP],
            ],
            'heading in a heading'        => [
                fn () => $e('h1', $e('h2')),
                ['h1 > h2: <h2> is not allowed directly inside of <h1>. '.$inP],
            ],
            'list item in a div'          => [
                fn () => $e('div', $e('li')),
                ['div > li: <li> is not allowed directly inside of <div>. Allowed there: flow content.'],
            ],
            'cell outside of a row'       => [
                fn () => $e('table', $e('td')),
                [
                    'table > td: <td> is not allowed directly inside of <table>. Allowed there: <caption>, '
                    .'<colgroup>, <thead>, <tbody>, <tfoot>, <tr>, script-supporting elements.',
                ],
            ],
            'div in a row'                => [
                fn () => $e('tr', [$e('td'), $e('div'), 'x']),
                [
                    'tr > div: <div> is not allowed directly inside of <tr>. Allowed there: <th>, <td>, script-supporting elements.',
                    'tr > (text): Text is not allowed directly inside of <tr>. Allowed there: <th>, <td>, script-supporting elements.',
                ],
            ],
            'body in a div'               => [
                fn () => $e('div', $e('body')),
                ['div > body: <body> is not allowed directly inside of <div>. Allowed there: flow content.'],
            ],
            'div in html'                 => [
                fn () => $e('html', [$e('head', $e('title', 't')), $e('div')]),
                ['html > div: <div> is not allowed directly inside of <html>. Allowed there: <head>, <body>.'],
            ],
            'paragraph in head'           => [
                fn () => $e('head', [$e('title', 't'), $e('p'), 'x']),
                [
                    'head > p: <p> is not allowed directly inside of <head>. Allowed there: metadata content.',
                    'head > (text): Text is not allowed directly inside of <head>. Allowed there: metadata content.',
                ],
            ],
            'title in body'               => [
                fn () => $e('body', $e('title', 't')),
                ['body > title: <title> is not allowed directly inside of <body>. Allowed there: flow content.'],
            ],
            'plain meta and link in body' => [
                fn () => $e('div', [$e('meta'), $e('link', null, ['rel' => 'icon'])]),
                [
                    'div > meta: <meta> is not allowed directly inside of <div>. Allowed there: flow content.',
                    'div > link: <link> is not allowed directly inside of <div>. Allowed there: flow content.',
                ],
            ],
            'content in an iframe'        => [
                fn () => $e('iframe', ['x', $e('p')]),
                [
                    'iframe > (text): Text is not allowed directly inside of <iframe>. '.$nothing,
                    'iframe > p: <p> is not allowed directly inside of <iframe>. '.$nothing,
                ],
            ],
            'element in an rp'            => [
                fn () => $e('rp', ['(', $e('b')]),
                ['rp > b: <b> is not allowed directly inside of <rp>. Allowed there: text.'],
            ],
            'paragraph in noscript in head' => [
                fn () => $e('head', [$e('title', 't'), $e('noscript', [$e('meta'), $e('p'), 'x'])]),
                [
                    'head > noscript > p: <p> is not allowed directly inside of <noscript>. Allowed there: <link>, <style>, <meta>.',
                    'head > noscript > (text): Text is not allowed directly inside of <noscript>. Allowed there: <link>, <style>, <meta>.',
                ],
            ],
            'paragraph in a div in a dl'  => [
                fn () => $e('dl', $e('div', [$e('dt'), $e('dd'), $e('p'), 'x'])),
                [
                    'dl > div > p: <p> is not allowed directly inside of <div>. Allowed there: <dt>, <dd>, script-supporting elements.',
                    'dl > div > (text): Text is not allowed directly inside of <div>. Allowed there: <dt>, <dd>, script-supporting elements.',
                ],
            ],
            'term in an ordinary div'     => [
                fn () => $e('section', $e('div', $e('dt'))),
                ['section > div > dt: <dt> is not allowed directly inside of <div>. Allowed there: flow content.'],
            ],
            'block in a link in a p'      => [
                fn () => $e('p', $e('a', $e('div'))),
                ['p > a > div: <div> is not allowed directly inside of <a>. '.$inP],
            ],
            'block through three layers'  => [
                fn () => $e('p', $e('ins', $e('a', $e('del', $e('div'))))),
                ['p > ins > a > del > div: <div> is not allowed directly inside of <del>. '.$inP],
            ],
            'text in a link in a list'    => [
                fn () => $e('ul', $e('a', ['x', $e('li')])),
                [
                    'ul > a: <a> is not allowed directly inside of <ul>. '.$inUl,
                    'ul > a > (text): Text is not allowed directly inside of <a>. '.$inUl,
                ],
            ],
            'block in a video in a p'     => [
                fn () => $e('p', $e('video', [$e('source'), $e('div')])),
                [
                    'p > video > div: <div> is not allowed directly inside of <video>. '
                    .'Allowed there: <source>, <track>, phrasing content.',
                ],
            ],
            'block in a map in a p'       => [
                fn () => $e('p', $e('map', $e('div'))),
                ['p > map > div: <div> is not allowed directly inside of <map>. Allowed there: <area>, phrasing content.'],
            ],
            'custom element in a list'    => [
                fn () => $e('ul', $e('my-item', $e('li'))),
                ['ul > my-item: <my-item> is not allowed directly inside of <ul>. '.$inUl],
            ],
            'block in a custom el in a p' => [
                fn () => $e('p', $e('my-box', $e('div'))),
                ['p > my-box > div: <div> is not allowed directly inside of <my-box>. '.$inP],
            ],
            'block four layers down'      => [
                fn () => $e('div', $e('p', $e('a', $e('ins', $e('div'))))),
                ['div > p > a > ins > div: <div> is not allowed directly inside of <ins>. '.$inP],
            ],
            'a map in a map'              => [
                fn () => $e('div', $e('map', $e('map', $e('li')))),
                ['div > map > map > li: <li> is not allowed directly inside of <map>. Allowed there: <area>, flow content.'],
            ],
            'after a fragment'            => [
                fn () => $e('ul', [Domo::fragment($e('li')), $e('p'), Domo::fragment(), 'x']),
                [
                    'ul > p: <p> is not allowed directly inside of <ul>. '.$inUl,
                    'ul > (text): Text is not allowed directly inside of <ul>. '.$inUl,
                ],
            ],
            'fragment of the wrong thing' => [
                fn () => $e('ul', Domo::fragment($e('li'), Domo::fragment($e('p'), 'x'))),
                [
                    'ul > p: <p> is not allowed directly inside of <ul>. '.$inUl,
                    'ul > (text): Text is not allowed directly inside of <ul>. '.$inUl,
                ],
            ],
            'inside a root fragment'      => [
                fn () => Domo::fragment($e('li'), $e('ul', $e('p'))),
                ['ul > p: <p> is not allowed directly inside of <ul>. '.$inUl],
            ],
            'doctype inside an element'   => [
                fn () => $e('div', Domo::doctype()),
                ['div > (doctype): A doctype belongs at the very top of a document, not inside of an element.'],
            ],
            'doctype inside a transparent' => [
                fn () => $e('a', Domo::fragment(Domo::doctype())),
                ['a > (doctype): A doctype belongs at the very top of a document, not inside of an element.'],
            ],
            'many problems, in order'     => [
                fn () => $e('div', [
                    $e('ul', [$e('li', $e('li')), $e('span')]),
                    $e('p', $e('table')),
                    $e('select', $e('p')),
                ]),
                [
                    'div > ul > li > li: <li> is not allowed directly inside of <li>. Allowed there: flow content.',
                    'div > ul > span: <span> is not allowed directly inside of <ul>. '.$inUl,
                    'div > p > table: <table> is not allowed directly inside of <p>. '.$inP,
                    'div > select > p: <p> is not allowed directly inside of <select>. Allowed there: <option>, <optgroup>, '
                    .'<hr>, script-supporting elements, <noscript>, <div>, <button>.',
                ],
            ],
            'problems below a problem'    => [
                fn () => $e('ul', $e('p', $e('div', $e('ul', 'x')))),
                [
                    'ul > p: <p> is not allowed directly inside of <ul>. '.$inUl,
                    'ul > p > div: <div> is not allowed directly inside of <p>. '.$inP,
                    'ul > p > div > ul > (text): Text is not allowed directly inside of <ul>. '.$inUl,
                ],
            ],
            'option and legend wording'   => [
                fn () => $e('div', [$e('option', $e('ul')), $e('legend', $e('ul'))]),
                [
                    'div > option: <option> is not allowed directly inside of <div>. Allowed there: flow content.',
                    'div > option > ul: <ul> is not allowed directly inside of <option>. Allowed there: text, <div>, phrasing content.',
                    'div > legend: <legend> is not allowed directly inside of <div>. Allowed there: flow content.',
                    'div > legend > ul: <ul> is not allowed directly inside of <legend>. Allowed there: phrasing content, heading content.',
                ],
            ],
        ];
    }//end invalidTrees()

    /**
     * Tests that each violation is found, described and located, and that validated rendering refuses.
     *
     * @param callable $build    Builds the tree.
     * @param string[] $expected The violations, as lines of text.
     *
     * @return void
     */
    #[DataProvider('invalidTrees')]
    public function testInvalidTreesAreReported(callable $build, array $expected): void
    {
        $root = $build();

        $this->assertSame($expected, self::problems($root));
        $this->assertSame($expected, array_map('strval', $root->validate()));

        // Without validation it renders as it always has.
        $this->assertIsString($root->render());
        $this->assertSame($root->render(), $root->render(false));
        $this->assertSame($root->render(), (string) $root);

        try {
            $root->render(true);
            $this->fail('Invalid content was rendered with validation on.');
        } catch (InvalidContentException $e) {
            $this->assertSame("The content is not valid HTML:\n  - ".implode("\n  - ", $expected), $e->getMessage());
            $this->assertSame($expected, array_map('strval', $e->getViolations()));
        }

        $this->expectException(InvalidContentException::class);

        Validator::assertValid($root);
    }//end testInvalidTreesAreReported()

    /**
     * Tests what a violation carries with it.
     *
     * @return void
     */
    public function testViolationsPointAtTheNode(): void
    {
        $p    = self::el('p', 'x');
        $text = Domo::text('loose');
        $ul   = self::el('ul', [$p, $text]);

        $violations = $ul->validate();

        $this->assertCount(2, $violations);
        $this->assertContainsOnlyInstancesOf(Violation::class, $violations);
        $this->assertSame([0, 1], array_keys($violations));

        $this->assertSame($p, $violations[0]->getNode());
        $this->assertSame('ul > p', $violations[0]->getPath());
        $this->assertSame(
            '<p> is not allowed directly inside of <ul>. Allowed there: <li>, script-supporting elements.',
            $violations[0]->getMessage()
        );
        $this->assertSame($violations[0]->getPath().': '.$violations[0]->getMessage(), (string) $violations[0]);
        $this->assertInstanceOf(\Stringable::class, $violations[0]);

        $this->assertSame($text, $violations[1]->getNode());
        $this->assertSame('ul > (text)', $violations[1]->getPath());
    }//end testViolationsPointAtTheNode()

    /**
     * Tests the exception on its own.
     *
     * @return void
     */
    public function testExceptionCarriesItsViolations(): void
    {
        $one = new Violation(Domo::text('a'), 'p > (text)', 'First.');
        $two = new Violation(Domo::text('b'), 'p', 'Second.');

        $exception = new InvalidContentException(['x' => $one, 'y' => $two]);

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertSame([$one, $two], $exception->getViolations());
        $this->assertSame("The content is not valid HTML:\n  - p > (text): First.\n  - p: Second.", $exception->getMessage());
    }//end testExceptionCarriesItsViolations()

    /**
     * Tests that fixing the tree fixes the result: nothing is remembered between runs.
     *
     * @return void
     */
    public function testValidationReflectsTheTreeAsItIs(): void
    {
        $ul = self::el('ul', 'loose text');

        $this->assertCount(1, $ul->validate());

        $ul->removeChildren()->appendChild(self::el('li', 'item'));

        $this->assertSame([], $ul->validate());
        $this->assertSame('<ul><li>item</li></ul>', $ul->render(true));

        $ul->appendChild(self::el('div'));

        $this->assertCount(1, $ul->validate());
    }//end testValidationReflectsTheTreeAsItIs()

    /**
     * Tests that validation happens once, at the node that was asked, and covers everything below.
     *
     * @return void
     */
    public function testValidatedRenderCoversTheWholeTree(): void
    {
        $deep = self::el('ul', self::el('p'));
        $root = self::el('div', self::el('section', self::el('article', $deep)));

        try {
            $root->render(true);
            $this->fail('A problem deep in the tree was missed.');
        } catch (InvalidContentException $e) {
            $this->assertCount(1, $e->getViolations());
            $this->assertSame('div > section > article > ul > p', $e->getViolations()[0]->getPath());
        }

        // A subtree is judged on its own terms.
        $this->assertSame('<p></p>', $deep->getChildren()[0]->render(true));
    }//end testValidatedRenderCoversTheWholeTree()

    /**
     * Tests that fragments validate on render as elements do.
     *
     * @return void
     */
    public function testFragmentsValidateOnRender(): void
    {
        $good = Domo::fragment(Domo::doctype(), self::el('html', [self::el('head', self::el('title', 'T')), self::el('body')]));

        $this->assertSame('<!DOCTYPE html><html><head><title>T</title></head><body></body></html>', $good->render(true));
        $this->assertSame([], $good->validate());

        $bad = Domo::fragment(self::el('ul', 'x'));

        $this->assertCount(1, $bad->validate());
        $this->assertSame('<ul>x</ul>', $bad->render());

        $this->expectException(InvalidContentException::class);

        $bad->render(true);
    }//end testFragmentsValidateOnRender()

    /**
     * Tests that the nodes with nothing inside of them accept the argument, and ignore it.
     *
     * @return void
     */
    public function testLeafNodesAcceptTheArgument(): void
    {
        $this->assertSame('a &lt; b', Domo::text('a < b')->render(true));
        $this->assertSame('<ul><p></p></ul>', Domo::raw('<ul><p></p></ul>')->render(true));
        $this->assertSame('<!--x-->', Domo::comment('x')->render(true));
        $this->assertSame('<!DOCTYPE html>', Domo::doctype()->render(true));
        $this->assertSame('<br />', self::el('br')->render(true));
        $this->assertSame([], self::el('br')->validate());

        // Validation is something you ask for: nobody's `render` does it unprompted.
        $nodes = [Domo::text('a'), Domo::raw('a'), Domo::comment('a'), Domo::doctype(), Domo::fragment(), self::el('br'), self::el('div')];

        foreach ($nodes as $node) {
            $this->assertSame($node->render(false), $node->render());

            $parameters = (new \ReflectionMethod($node, 'render'))->getParameters();

            $this->assertCount(1, $parameters);
            $this->assertSame('validate', $parameters[0]->getName());
            $this->assertFalse($parameters[0]->getDefaultValue(), get_class($node));
        }
    }//end testLeafNodesAcceptTheArgument()

    /**
     * Tests every element against its own content model, one child at a time.
     *
     * For each of the 115 elements as a parent inside of a <div>, and each of the 115 as its
     * only child, the validator must agree with a plain reading of the content model.
     *
     * @return void
     */
    public function testEveryPairOfElementsAgreesWithTheContentModels(): void
    {
        $names = array_keys(Elements::all());
        $pairs = 0;

        foreach ($names as $parentName) {
            $model = ContentModels::get($parentName);

            if ([] === $model && false === (Domo::createElement($parentName) instanceof EnclosingElement)) {
                continue;
            }

            if (true === in_array($parentName, ['title', 'textarea', 'script', 'style'], true)) {
                // These refuse child elements outright, before validation ever sees them.
                continue;
            }

            // Inside of a <div>, a transparent parent passes on "flow content".
            if (true === in_array(ContentModels::TRANSPARENT, $model, true)) {
                $model = array_merge(array_diff($model, [ContentModels::TRANSPARENT]), ['flow']);
            }

            foreach ($names as $childName) {
                $child  = Domo::createElement($childName);
                $parent = Domo::createElement($parentName, [], $child);
                $root   = Domo::createElement('div', [], $parent);

                $allowed = (true === in_array(ContentModels::ANYTHING, $model, true)
                    || true === in_array($childName, $model, true)
                    || [] !== array_intersect(\Cam5\Domoarigato\Validation\Categorizer::categoriesOf($child), $model));

                $found = array_filter(
                    $root->validate(),
                    fn (Violation $violation) => $violation->getNode() === $child
                        && true === str_contains($violation->getMessage(), 'directly inside of')
                );

                $this->assertSame($allowed, ([] === $found), '<'.$childName.'> inside of <'.$parentName.'>');

                $pairs++;
            }
        }//end foreach

        $this->assertSame((98 * 115), $pairs);
    }//end testEveryPairOfElementsAgreesWithTheContentModels()
}//end class
