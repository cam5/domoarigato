<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Enums\ForbiddenDescendants;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Validation\AncestorRules;
use Cam5\Domoarigato\Validation\StructureRules;
use Cam5\Domoarigato\Validation\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rules that reach beyond a parent and its child: forbidden descendants, required
 * ancestors, and the number and order of an element's children.
 */
#[CoversClass(Validator::class)]
#[CoversClass(AncestorRules::class)]
#[CoversClass(StructureRules::class)]
#[CoversClass(ForbiddenDescendants::class)]
final class ValidationRulesTest extends TestCase
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
     * Tests the table of forbidden descendants itself, written out again by hand.
     *
     * @return void
     */
    public function testForbiddenDescendantsTable(): void
    {
        $sections = ['header', 'footer', 'heading', 'sectioning'];

        $this->assertSame(
            [
                'a'        => ['a', 'interactive'],
                'address'  => ['address', 'header', 'footer', 'heading', 'sectioning'],
                'audio'    => ['audio', 'video'],
                'button'   => ['interactive'],
                'caption'  => ['table'],
                'dfn'      => ['dfn'],
                'dt'       => $sections,
                'footer'   => ['header', 'footer'],
                'form'     => ['form'],
                'header'   => ['header', 'footer'],
                'label'    => ['label'],
                'meter'    => ['meter'],
                'noscript' => ['noscript'],
                'progress' => ['progress'],
                'th'       => $sections,
                'video'    => ['audio', 'video'],
            ],
            ForbiddenDescendants::all()
        );

        foreach (ForbiddenDescendants::all() as $name => $forbidden) {
            $this->assertTrue(Elements::contains($name));

            foreach ($forbidden as $entry) {
                $this->assertTrue(Elements::contains($entry) || Categories::contains($entry), $entry);
            }
        }

        $this->assertSame(['html', 'body', 'div', 'form'], AncestorRules::AROUND_MAIN);
    }//end testForbiddenDescendantsTable()

    /**
     * Elements nested where an ancestor forbids them, with the complaint we expect.
     *
     * Each case: the ancestor, what goes between it and the element (if anything), the element.
     *
     * @return array
     */
    public static function forbiddenNestings(): array
    {
        $e = fn (...$args) => self::el(...$args);

        $named    = fn ($child, $ancestor) => '<'.$child.'> is not allowed anywhere inside of <'.$ancestor.'>.';
        $category = fn ($child, $kind, $ancestor) => '<'.$child.'> is '.$kind.' content, which is not allowed anywhere inside of <'.$ancestor.'>.';

        return [
            'link in a link'               => ['a', null, fn () => $e('a', 'x', ['href' => '/']), $named('a', 'a')],
            'placeholder link in a link'   => ['a', null, fn () => $e('a', 'x'), $named('a', 'a')],
            'link deep in a link'          => ['a', 'span', fn () => $e('a', 'x'), $named('a', 'a')],
            'button in a link'             => ['a', null, fn () => $e('button', 'x'), $category('button', 'interactive', 'a')],
            'button deep in a link'        => ['a', 'em', fn () => $e('button', 'x'), $category('button', 'interactive', 'a')],
            'input in a link'              => ['a', null, fn () => $e('input'), $category('input', 'interactive', 'a')],
            'select in a link'             => ['a', null, fn () => $e('select'), $category('select', 'interactive', 'a')],
            'label in a link'              => ['a', null, fn () => $e('label'), $category('label', 'interactive', 'a')],
            'iframe in a link'             => ['a', null, fn () => $e('iframe'), $category('iframe', 'interactive', 'a')],
            'controls video in a link'     => ['a', null, fn () => $e('video', null, ['controls' => true]), $category('video', 'interactive', 'a')],
            'usemap image in a link'       => ['a', null, fn () => $e('img', null, ['usemap' => '#m']), $category('img', 'interactive', 'a')],
            'link in a button'             => ['button', null, fn () => $e('a', 'x', ['href' => '/']), $category('a', 'interactive', 'button')],
            'button in a button'           => ['button', null, fn () => $e('button'), $category('button', 'interactive', 'button')],
            'input deep in a button'       => ['button', 'span', fn () => $e('input'), $category('input', 'interactive', 'button')],
            'textarea in a button'         => ['button', null, fn () => $e('textarea'), $category('textarea', 'interactive', 'button')],
            'label in a label'             => ['label', null, fn () => $e('label'), $named('label', 'label')],
            'label deep in a label'        => ['label', 'span', fn () => $e('label'), $named('label', 'label')],
            'form in a form'               => ['form', null, fn () => $e('form'), $named('form', 'form')],
            'form deep in a form'          => ['form', 'div', fn () => $e('form'), $named('form', 'form')],
            'header in a header'           => ['header', null, fn () => $e('header'), $named('header', 'header')],
            'footer in a header'           => ['header', 'div', fn () => $e('footer'), $named('footer', 'header')],
            'header in a footer'           => ['footer', null, fn () => $e('header'), $named('header', 'footer')],
            'footer in a footer'           => ['footer', 'div', fn () => $e('footer'), $named('footer', 'footer')],
            'address in an address'        => ['address', null, fn () => $e('address'), $named('address', 'address')],
            'header in an address'         => ['address', null, fn () => $e('header'), $named('header', 'address')],
            'footer in an address'         => ['address', 'div', fn () => $e('footer'), $named('footer', 'address')],
            'heading in an address'        => ['address', null, fn () => $e('h2'), $category('h2', 'heading', 'address')],
            'hgroup in an address'         => ['address', null, fn () => $e('hgroup'), $category('hgroup', 'heading', 'address')],
            'section in an address'        => ['address', 'div', fn () => $e('section'), $category('section', 'sectioning', 'address')],
            'nav in an address'            => ['address', null, fn () => $e('nav'), $category('nav', 'sectioning', 'address')],
            'heading in a term'            => ['dt', null, fn () => $e('h3'), $category('h3', 'heading', 'dt')],
            'article in a term'            => ['dt', 'div', fn () => $e('article'), $category('article', 'sectioning', 'dt')],
            'header in a term'             => ['dt', null, fn () => $e('header'), $named('header', 'dt')],
            'footer in a term'             => ['dt', null, fn () => $e('footer'), $named('footer', 'dt')],
            'heading in a header cell'     => ['th', null, fn () => $e('h1'), $category('h1', 'heading', 'th')],
            'aside in a header cell'       => ['th', 'div', fn () => $e('aside'), $category('aside', 'sectioning', 'th')],
            'header in a header cell'      => ['th', null, fn () => $e('header'), $named('header', 'th')],
            'footer in a header cell'      => ['th', null, fn () => $e('footer'), $named('footer', 'th')],
            'dfn in a dfn'                 => ['dfn', 'em', fn () => $e('dfn'), $named('dfn', 'dfn')],
            'table in a caption'           => ['caption', 'div', fn () => $e('table'), $named('table', 'caption')],
            'progress in a progress'       => ['progress', 'span', fn () => $e('progress'), $named('progress', 'progress')],
            'meter in a meter'             => ['meter', null, fn () => $e('meter'), $named('meter', 'meter')],
            'noscript in a noscript'       => ['noscript', 'div', fn () => $e('noscript'), $named('noscript', 'noscript')],
            'video in a video'             => ['video', null, fn () => $e('video'), $named('video', 'video')],
            'audio in a video'             => ['video', 'div', fn () => $e('audio'), $named('audio', 'video')],
            'video in an audio'            => ['audio', null, fn () => $e('video'), $named('video', 'audio')],
            'audio in an audio'            => ['audio', 'div', fn () => $e('audio'), $named('audio', 'audio')],
        ];
    }//end forbiddenNestings()

    /**
     * Tests that each forbidden nesting is reported, once, against the right node.
     *
     * @param string      $ancestor The tag name of the ancestor that objects.
     * @param string|null $between  The tag name of an element in between, if any.
     * @param callable    $build    Builds the element that is out of place.
     * @param string      $expected The complaint.
     *
     * @return void
     */
    #[DataProvider('forbiddenNestings')]
    public function testForbiddenDescendantsAreReported(string $ancestor, ?string $between, callable $build, string $expected): void
    {
        $child  = $build();
        $inner  = (null === $between) ? $child : self::el($between, $child);
        $root   = self::el($ancestor, $inner);
        $prefix = $ancestor.' > '.((null === $between) ? '' : $between.' > ').$child->getTagName().': ';

        $found = array_values(array_filter(
            Validator::validate($root),
            fn ($violation) => str_contains($violation->getMessage(), 'anywhere inside of')
        ));

        $this->assertCount(1, $found);
        $this->assertSame($prefix.$expected, (string) $found[0]);
        $this->assertSame($child, $found[0]->getNode());
    }//end testForbiddenDescendantsAreReported()

    /**
     * Nestings that look like the forbidden ones, and aren't.
     *
     * @return array
     */
    public static function allowedNestings(): array
    {
        $e = fn (...$args) => self::el(...$args);

        return [
            'hidden input in a link'     => [fn () => $e('div', $e('a', $e('input', null, ['type' => 'hidden'])))],
            'plain image in a link'      => [fn () => $e('div', $e('a', $e('img')))],
            'video w/o controls in link' => [fn () => $e('div', $e('a', $e('video')))],
            'placeholder link in button' => [fn () => $e('button', $e('a', 'x'))],
            'span in a button'           => [fn () => $e('button', $e('span', $e('em', 'x')))],
            'input in a label'           => [fn () => $e('label', ['Name ', $e('input')])],
            'link beside a link'         => [fn () => $e('p', [$e('a', 'x'), $e('a', 'y')])],
            'form beside a form'         => [fn () => $e('div', [$e('form'), $e('form')])],
            'section in a header'        => [fn () => $e('header', $e('section', $e('h1', 'x')))],
            'heading in a header'        => [fn () => $e('header', [$e('h1', 'x'), $e('nav')])],
            'header in a section'        => [fn () => $e('section', [$e('header'), $e('footer')])],
            'paragraph in an address'    => [fn () => $e('address', $e('p', $e('a', 'x', ['href' => '/'])))],
            'heading in a definition'    => [fn () => $e('dd', [$e('h2', 'x'), $e('section'), $e('header')])],
            'heading in a data cell'     => [fn () => $e('td', [$e('h2', 'x'), $e('section'), $e('footer')])],
            'paragraph in a term'        => [fn () => $e('dt', $e('p', 'x'))],
            'list in a caption'          => [fn () => $e('caption', $e('ul', $e('li')))],
            'meter in a progress'        => [fn () => $e('progress', $e('meter'))],
            'progress in a meter'        => [fn () => $e('meter', $e('progress'))],
            'label in a dfn'             => [fn () => $e('dfn', $e('abbr', 'x'))],
            'main in a div in a form'    => [fn () => $e('html', $e('body', $e('form', $e('div', $e('main')))))],
            'main in a custom element'   => [fn () => $e('body', $e('app-shell', $e('main')))],
            'main on its own'            => [fn () => $e('main', $e('article', $e('header')))],
            'area in a map'              => [fn () => $e('map', $e('area'))],
            'area deep in a map'         => [fn () => $e('div', $e('map', $e('p', $e('span', $e('area')))))],
            'area on its own'            => [fn () => $e('area')],
            'area in a root fragment'    => [fn () => Domo::fragment($e('area'))],
            'forbidden, but in a template' => [fn () => $e('a', $e('template', $e('a', $e('button'))))],
            'forbidden, but in an svg'   => [fn () => $e('a', $e('svg', $e('a')))],
            'forbidden, but raw markup'  => [fn () => $e('form', Domo::raw('<form></form>'))],
        ];
    }//end allowedNestings()

    /**
     * Tests that the near misses pass.
     *
     * @param callable $build Builds the tree.
     *
     * @return void
     */
    #[DataProvider('allowedNestings')]
    public function testAllowedNestingsPass(callable $build): void
    {
        $this->assertSame([], self::problems($build()));
    }//end testAllowedNestingsPass()

    /**
     * Tests that the nearest objecting ancestor is the one named, and only one complaint is made.
     *
     * @return void
     */
    public function testNearestAncestorIsNamed(): void
    {
        $tree = self::el('header', self::el('footer', self::el('div', self::el('header'))));

        $this->assertSame(
            [
                'header > footer: <footer> is not allowed anywhere inside of <header>.',
                'header > footer > div > header: <header> is not allowed anywhere inside of <footer>.',
            ],
            self::problems($tree)
        );

        // An element can be wrong for its parent and for an ancestor, and is told about both.
        $this->assertSame(
            [
                'a > p > a: <a> is not allowed anywhere inside of <a>.',
                'a > p > a > div: <div> is not allowed directly inside of <a>. Allowed there: phrasing content.',
            ],
            self::problems(self::el('a', self::el('p', self::el('a', self::el('div')))))
        );

        $this->assertSame(
            [
                'ul > form: <form> is not allowed directly inside of <ul>. Allowed there: <li>, script-supporting elements.',
                'ul > form > form: <form> is not allowed anywhere inside of <form>.',
            ],
            self::problems(self::el('ul', self::el('form', self::el('form'))))
        );
    }//end testNearestAncestorIsNamed()

    /**
     * Tests the two elements that need the right ancestors, rather than the absence of wrong ones.
     *
     * @return void
     */
    public function testMainAndArea(): void
    {
        $around = 'It may only be inside of <html>, <body>, <div>, <form> and custom elements.';

        $this->assertSame(
            ['article > main: <main> is not allowed anywhere inside of <article>. '.$around],
            self::problems(self::el('article', self::el('main')))
        );
        $this->assertSame(
            ['body > section > div > main: <main> is not allowed anywhere inside of <section>. '.$around],
            self::problems(self::el('body', self::el('section', self::el('div', self::el('main')))))
        );
        $this->assertSame(
            ['aside > nav > main: <main> is not allowed anywhere inside of <nav>. '.$around],
            self::problems(self::el('aside', self::el('nav', self::el('main'))))
        );
        $this->assertSame(
            ['div > area: <area> is only allowed somewhere inside of a <map>.'],
            self::problems(self::el('div', self::el('area')))
        );
        $this->assertSame(
            ['p > span > area: <area> is only allowed somewhere inside of a <map>.'],
            self::problems(self::el('p', self::el('span', self::el('area'))))
        );

        // The rules for <main> are only about <main>.
        $this->assertSame([], self::problems(self::el('article', self::el('div', self::el('section')))));
    }//end testMainAndArea()

    /**
     * Arrangements of children that follow the rules.
     *
     * @return array
     */
    public static function validStructures(): array
    {
        $e = fn (...$args) => self::el(...$args);

        return [
            'html, head then body'        => [fn () => $e('html', [$e('head', $e('title', 't')), $e('body')])],
            'html, body only'             => [fn () => $e('html', $e('body'))],
            'html, head only'             => [fn () => $e('html', $e('head', $e('title', 't')))],
            'html, empty'                 => [fn () => $e('html')],
            'head, title and base'        => [fn () => $e('head', [$e('base'), $e('meta'), $e('title', 't'), $e('link')])],
            'head, title in a fragment'   => [fn () => $e('head', Domo::fragment($e('meta'), Domo::fragment($e('title', 't'))))],
            'head, raw markup'            => [fn () => $e('head', Domo::raw('<title>t</title>'))],
            'head, raw markup in a fragment' => [fn () => $e('head', Domo::fragment(Domo::fragment(Domo::raw('<title>t</title>'))))],
            'table, everything in order'  => [
                fn () => $e('table', [
                    $e('caption'), $e('colgroup'), $e('colgroup'), $e('thead'), $e('tbody'), $e('tbody'), $e('tfoot'),
                ]),
            ],
            'table, rows only'            => [fn () => $e('table', [$e('tr'), $e('tr')])],
            'table, head then rows'       => [fn () => $e('table', [$e('thead'), $e('tr'), $e('tfoot')])],
            'table, scripts in between'   => [fn () => $e('table', [$e('script'), $e('thead'), $e('template'), $e('tbody'), $e('script')])],
            'table, empty'                => [fn () => $e('table')],
            'details, summary first'      => [fn () => $e('details', [$e('summary', 's'), $e('p', 'x')])],
            'details, text before summary' => [fn () => $e('details', ["\n", Domo::comment('c'), $e('summary', 's')])],
            'details, script first'       => [fn () => $e('details', [$e('script'), $e('summary', 's')])],
            'details, raw markup'         => [fn () => $e('details', [Domo::raw('<summary>s</summary>'), $e('p')])],
            'fieldset, no legend'         => [fn () => $e('fieldset', $e('input'))],
            'fieldset, legend first'      => [fn () => $e('fieldset', [$e('legend', 'l'), $e('input')])],
            'fieldset, empty'             => [fn () => $e('fieldset')],
            'figure, no caption'          => [fn () => $e('figure', $e('img'))],
            'figure, caption first'       => [fn () => $e('figure', [$e('figcaption'), $e('img'), $e('p')])],
            'figure, caption last'        => [fn () => $e('figure', [$e('img'), $e('p'), $e('figcaption')])],
            'figure, caption alone'       => [fn () => $e('figure', $e('figcaption'))],
            'picture, sources then img'   => [fn () => $e('picture', [$e('source'), $e('source'), $e('img')])],
            'picture, img only'           => [fn () => $e('picture', [$e('script'), $e('img')])],
            'hgroup, heading and text'    => [fn () => $e('hgroup', [$e('p'), $e('h2'), $e('p')])],
            'video, sources then tracks'  => [fn () => $e('video', [$e('source'), $e('source'), $e('track'), $e('track'), $e('p')])],
            'video, src and tracks'       => [fn () => $e('video', [$e('track'), $e('p')], ['src' => 'a.mp4'])],
            'audio, fallback only'        => [fn () => $e('audio', $e('p'))],
            'audio, tracks only'          => [fn () => $e('audio', [$e('track'), $e('track')])],
            'colgroup, cols'              => [fn () => $e('colgroup', [$e('col'), $e('col')])],
            'colgroup, span and nothing'  => [fn () => $e('colgroup', null, ['span' => 2])],
            'colgroup, span and template' => [fn () => $e('colgroup', $e('template'), ['span' => 2])],
            'colgroup, span switched off' => [fn () => $e('colgroup', $e('col'), ['span' => null])],
            'dl, empty'                   => [fn () => $e('dl')],
            'dl, one group'               => [fn () => $e('dl', [$e('dt'), $e('dd')])],
            'dl, groups of several'       => [fn () => $e('dl', [$e('dt'), $e('dt'), $e('dd'), $e('dt'), $e('dd'), $e('dd')])],
            'dl, groups in divs'          => [fn () => $e('dl', [$e('div', [$e('dt'), $e('dd')]), $e('div', [$e('dt'), $e('dt'), $e('dd'), $e('dd')])])],
            'dl, scripts in between'      => [fn () => $e('dl', [$e('dt'), $e('script'), $e('dd'), $e('template')])],
            'dl, raw markup'              => [fn () => $e('dl', [$e('dd'), Domo::raw('<dt></dt>')])],
            'dl div, raw markup'          => [fn () => $e('dl', $e('div', Domo::raw('<dt></dt><dd></dd>')))],
            'ordinary div with nothing'   => [fn () => $e('section', $e('div'))],
            'elements without such rules' => [fn () => $e('div', [$e('p'), $e('p'), $e('ul', [$e('li'), $e('li')])])],
        ];
    }//end validStructures()

    /**
     * Tests that well arranged children pass.
     *
     * @param callable $build Builds the tree.
     *
     * @return void
     */
    #[DataProvider('validStructures')]
    public function testValidStructuresPass(callable $build): void
    {
        $this->assertSame([], self::problems($build()));
    }//end testValidStructuresPass()

    /**
     * Arrangements of children that break the rules, with every violation we expect, in order.
     *
     * @return array
     */
    public static function invalidStructures(): array
    {
        $e = fn (...$args) => self::el(...$args);

        $groups = fn ($name) => 'Inside of <'.$name.'>, every group must be one or more <dt> followed by one or more <dd>.';

        return [
            'html, two heads'            => [
                fn () => $e('html', [$e('head', $e('title', 'a')), $e('head', $e('title', 'b')), $e('body')]),
                ['html > head: <html> may only have one <head>.'],
            ],
            'html, three bodies'         => [
                fn () => $e('html', [$e('body'), $e('body'), $e('body')]),
                ['html > body: <html> may only have one <body>.', 'html > body: <html> may only have one <body>.'],
            ],
            'html, body before head'     => [
                fn () => $e('html', [$e('body'), $e('head', $e('title', 't'))]),
                ['html > head: <head> must come before <body> inside of <html>.'],
            ],
            'head, no title'             => [
                fn () => $e('head', [$e('meta'), $e('link')]),
                ['head: <head> is missing its <title>.'],
            ],
            'head, empty'                => [fn () => $e('html', $e('head')), ['html > head: <head> is missing its <title>.']],
            'head, two titles'           => [
                fn () => $e('head', [$e('title', 'a'), $e('meta'), $e('title', 'b'), $e('title', 'c')]),
                ['head > title: <head> may only have one <title>.', 'head > title: <head> may only have one <title>.'],
            ],
            'head, two bases'            => [
                fn () => $e('head', [$e('base'), $e('title', 't'), $e('base')]),
                ['head > base: <head> may only have one <base>.'],
            ],
            'head, nothing right'        => [
                fn () => $e('head', [$e('base'), $e('base')]),
                ['head: <head> is missing its <title>.', 'head > base: <head> may only have one <base>.'],
            ],
            'table, two captions'        => [
                fn () => $e('table', [$e('caption'), $e('caption'), $e('tbody')]),
                ['table > caption: <table> may only have one <caption>.'],
            ],
            'table, two heads and feet'  => [
                fn () => $e('table', [$e('thead'), $e('thead'), $e('tbody'), $e('tfoot'), $e('tfoot')]),
                ['table > thead: <table> may only have one <thead>.', 'table > tfoot: <table> may only have one <tfoot>.'],
            ],
            'table, caption last'        => [
                fn () => $e('table', [$e('tbody'), $e('caption')]),
                ['table > caption: <caption> must come before <tbody> inside of <table>.'],
            ],
            'table, head after body'     => [
                fn () => $e('table', [$e('colgroup'), $e('tbody'), $e('thead'), $e('tbody'), $e('tfoot')]),
                ['table > thead: <thead> must come before <tbody> inside of <table>.'],
            ],
            'table, foot before body'    => [
                fn () => $e('table', [$e('tfoot'), $e('tr'), $e('colgroup')]),
                [
                    'table > tr: <tr> must come before <tfoot> inside of <table>.',
                    'table > colgroup: <colgroup> must come before <tfoot> inside of <table>.',
                ],
            ],
            'table, rows and bodies'     => [
                fn () => $e('table', [$e('tbody'), $e('tr'), $e('tr')]),
                ['table > tr: <table> may have <tbody> or <tr> elements, but not both.'],
            ],
            'details, no summary'        => [
                fn () => $e('details', $e('p', 'x')),
                ['details: <details> is missing its <summary>.'],
            ],
            'details, two summaries'     => [
                fn () => $e('details', [$e('summary'), $e('summary')]),
                ['details > summary: <details> may only have one <summary>.'],
            ],
            'details, summary second'    => [
                fn () => $e('div', $e('details', [$e('p'), $e('summary')])),
                ['div > details > summary: <summary> must be the first element inside of <details>.'],
            ],
            'fieldset, two legends'      => [
                fn () => $e('fieldset', [$e('legend'), $e('legend')]),
                ['fieldset > legend: <fieldset> may only have one <legend>.'],
            ],
            'fieldset, legend second'    => [
                fn () => $e('fieldset', [$e('input'), $e('legend')]),
                ['fieldset > legend: <legend> must be the first element inside of <fieldset>.'],
            ],
            'figure, two captions'       => [
                fn () => $e('figure', [$e('figcaption'), $e('img'), $e('figcaption')]),
                ['figure > figcaption: <figure> may only have one <figcaption>.'],
            ],
            'figure, caption in middle'  => [
                fn () => $e('figure', [$e('img'), $e('figcaption'), $e('img')]),
                ['figure > figcaption: <figcaption> must be the first or the last element inside of <figure>.'],
            ],
            'picture, no img'            => [
                fn () => $e('picture', $e('source')),
                ['picture: <picture> is missing its <img>.'],
            ],
            'picture, two imgs'          => [
                fn () => $e('picture', [$e('img'), $e('img')]),
                ['picture > img: <picture> may only have one <img>.'],
            ],
            'picture, source after img'  => [
                fn () => $e('picture', [$e('source'), $e('img'), $e('source')]),
                ['picture > source: <source> must come before <img> inside of <picture>.'],
            ],
            'hgroup, no heading'         => [
                fn () => $e('hgroup', $e('p')),
                ['hgroup: <hgroup> is missing its heading.'],
            ],
            'hgroup, two headings'       => [
                fn () => $e('hgroup', [$e('h1'), $e('p'), $e('h6')]),
                ['hgroup > h6: <hgroup> may only have one heading.'],
            ],
            'video, src and sources'     => [
                fn () => $e('video', [$e('source'), $e('source'), $e('track')], ['src' => 'a.mp4']),
                [
                    'video > source: <video> has a "src" attribute, so it may not have <source> elements.',
                    'video > source: <video> has a "src" attribute, so it may not have <source> elements.',
                ],
            ],
            'audio, src and a source'    => [
                fn () => $e('audio', $e('source'), ['src' => 'a.mp3']),
                ['audio > source: <audio> has a "src" attribute, so it may not have <source> elements.'],
            ],
            'video, track before source' => [
                fn () => $e('video', [$e('track'), $e('source')]),
                ['video > source: <source> must come before <track> inside of <video>.'],
            ],
            'audio, fallback first'      => [
                fn () => $e('div', $e('audio', [$e('p'), $e('source'), $e('track'), $e('em')])),
                [
                    'div > audio > source: <source> must come before <p> inside of <audio>.',
                    'div > audio > track: <track> must come before <p> inside of <audio>.',
                ],
            ],
            'video, two fallbacks first' => [
                fn () => $e('video', [$e('p'), $e('em'), $e('source')]),
                ['video > source: <source> must come before <p> inside of <video>.'],
            ],
            'colgroup, span and cols'    => [
                fn () => $e('colgroup', [$e('col'), $e('col')], ['span' => 2]),
                [
                    'colgroup > col: <colgroup> has a "span" attribute, so it may not have <col> elements.',
                    'colgroup > col: <colgroup> has a "span" attribute, so it may not have <col> elements.',
                ],
            ],
            'dl, description first'      => [fn () => $e('dl', [$e('dd'), $e('dt')]), ['dl: '.$groups('dl')]],
            'dl, term without a desc'    => [fn () => $e('dl', [$e('dt'), $e('dd'), $e('dt')]), ['dl: '.$groups('dl')]],
            'dl, only a term'            => [fn () => $e('dl', $e('dt')), ['dl: '.$groups('dl')]],
            'dl, only a description'     => [fn () => $e('section', $e('dl', $e('dd'))), ['section > dl: '.$groups('dl')]],
            'dl, divs and bare terms'    => [
                fn () => $e('dl', [$e('div', [$e('dt'), $e('dd')]), $e('dt'), $e('dd')]),
                [
                    'dl > dt: <dl> may have <div> or <dt> elements, but not both.',
                    'dl > dd: <dl> may have <div> or <dd> elements, but not both.',
                ],
            ],
            'dl div, empty'              => [fn () => $e('dl', $e('div')), ['dl > div: '.$groups('div')]],
            'dl div, two groups'         => [
                fn () => $e('dl', $e('div', [$e('dt'), $e('dd'), $e('dt'), $e('dd')])),
                ['dl > div: '.$groups('div')],
            ],
            'dl div, description first'  => [
                fn () => $e('dl', [$e('div', [$e('dt'), $e('dd')]), $e('div', [$e('dd'), $e('dt')])]),
                ['dl > div: '.$groups('div')],
            ],
            'through a fragment'         => [
                fn () => $e('details', Domo::fragment($e('p'), Domo::fragment($e('summary'), $e('summary')))),
                [
                    'details > summary: <details> may only have one <summary>.',
                    'details > summary: <summary> must be the first element inside of <details>.',
                ],
            ],
            'structure before children'  => [
                fn () => $e('table', [$e('tbody', $e('p')), $e('caption', $e('table'))]),
                [
                    'table > caption: <caption> must come before <tbody> inside of <table>.',
                    'table > tbody > p: <p> is not allowed directly inside of <tbody>. Allowed there: <tr>, script-supporting elements.',
                    'table > caption > table: <table> is not allowed anywhere inside of <caption>.',
                ],
            ],
        ];
    }//end invalidStructures()

    /**
     * Tests that each badly arranged child is found, described and located.
     *
     * @param callable $build    Builds the tree.
     * @param string[] $expected The violations, as lines of text.
     *
     * @return void
     */
    #[DataProvider('invalidStructures')]
    public function testInvalidStructuresAreReported(callable $build, array $expected): void
    {
        $this->assertSame($expected, self::problems($build()));
    }//end testInvalidStructuresAreReported()

    /**
     * Tests which node each kind of structural violation points at.
     *
     * @return void
     */
    public function testStructuralViolationsPointAtTheRightNode(): void
    {
        $first   = self::el('title', 'a');
        $second  = self::el('title', 'b');
        $head    = self::el('head', [$first, $second]);
        $details = self::el('details');
        $root    = self::el('html', [$head, self::el('body', $details)]);

        $violations = Validator::validate($root);

        $this->assertCount(2, $violations);
        $this->assertSame($second, $violations[0]->getNode());
        $this->assertSame('html > head > title', $violations[0]->getPath());
        $this->assertSame($details, $violations[1]->getNode());
        $this->assertSame('html > body > details', $violations[1]->getPath());
    }//end testStructuralViolationsPointAtTheRightNode()

    /**
     * Tests the orders that the structural rules go by.
     *
     * @return void
     */
    public function testOrders(): void
    {
        $this->assertSame(
            ['caption' => 0, 'colgroup' => 1, 'thead' => 2, 'tbody' => 3, 'tr' => 3, 'tfoot' => 4],
            StructureRules::TABLE_ORDER
        );
        $this->assertSame(['source' => 0, 'track' => 1], StructureRules::MEDIA_ORDER);
        $this->assertSame(['source' => 0, 'img' => 1], StructureRules::PICTURE_ORDER);
        $this->assertSame(['head' => 0, 'body' => 1], StructureRules::HTML_ORDER);
    }//end testOrders()

    /**
     * Tests that a whole, well-formed page passes every rule at once.
     *
     * @return void
     */
    public function testAWholePagePasses(): void
    {
        $e = fn (...$args) => self::el(...$args);

        $page = Domo::fragment(
            Domo::doctype(),
            $e('html', [
                $e('head', [$e('meta', null, ['charset' => 'utf-8']), $e('title', 'Shop'), $e('link', null, ['rel' => 'stylesheet'])]),
                $e('body', [
                    $e('header', [$e('h1', 'Shop'), $e('nav', $e('ul', $e('li', $e('a', 'Home', ['href' => '/']))))]),
                    $e('main', [
                        $e('article', [
                            $e('header', $e('h2', 'Tea')),
                            $e('figure', [$e('picture', [$e('source'), $e('img')]), $e('figcaption', 'A cup')]),
                            $e('table', [$e('caption', 'Prices'), $e('thead', $e('tr', $e('th', 'Size'))), $e('tbody', $e('tr', $e('td', 'S')))]),
                            $e('dl', [$e('dt', 'Origin'), $e('dd', 'Uji')]),
                            $e('details', [$e('summary', 'More'), $e('p', 'Text')]),
                        ]),
                        $e('form', $e('fieldset', [$e('legend', 'Order'), $e('label', ['Qty ', $e('input')]), $e('button', 'Buy')])),
                    ]),
                    $e('footer', $e('address', $e('a', 'Mail', ['href' => 'mailto:a@b.c']))),
                ]),
            ])
        );

        $this->assertSame([], self::problems($page));
        $this->assertStringStartsWith('<!DOCTYPE html><html><head>', $page->render(true));
    }//end testAWholePagePasses()
}//end class
