<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Nodes\Comment;
use Cam5\Domoarigato\Nodes\Doctype;
use Cam5\Domoarigato\Nodes\NodeInterface;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;
use Cam5\Domoarigato\Nodes\Traits\CastsToString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the nodes that aren't elements, and can't hold other nodes.
 */
#[CoversClass(Text::class)]
#[CoversClass(RawHtml::class)]
#[CoversClass(Comment::class)]
#[CoversClass(Doctype::class)]
#[CoversTrait(CastsToString::class)]
final class NodesTest extends TestCase
{
    /**
     * Text, and how it should be written between tags.
     *
     * @return array
     */
    public static function text(): array
    {
        return [
            'plain'             => ['Hello World', 'Hello World'],
            'empty'             => ['', ''],
            'the string "0"'    => ['0', '0'],
            'markup'            => ['<b>bold</b>', '&lt;b&gt;bold&lt;/b&gt;'],
            'script'            => ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'],
            'ampersand'         => ['Tom & Jerry', 'Tom &amp; Jerry'],
            'existing entity'   => ['&lt;', '&amp;lt;'],
            'numeric reference' => ['&#60;', '&amp;#60;'],
            'quotes untouched'  => ['"double" and \'single\'', '"double" and \'single\''],
            'whitespace kept'   => ["  a\n\tb  ", "  a\n\tb  "],
            'multibyte'         => ['日本語 ☃ 😀', '日本語 ☃ 😀'],
            'invalid utf-8'     => ["a\xC3\x28b", "a\u{FFFD}(b"],
            'lone greater than' => ['a > b', 'a &gt; b'],
            'comment opener'    => ['<!-- x -->', '&lt;!-- x --&gt;'],
            'cdata'             => ['<![CDATA[x]]>', '&lt;![CDATA[x]]&gt;'],
        ];
    }//end text()

    /**
     * Tests that text is escaped on the way out, and kept as given otherwise.
     *
     * @param string $raw      The text as given.
     * @param string $expected The text as rendered.
     *
     * @return void
     */
    #[DataProvider('text')]
    public function testTextIsEscaped(string $raw, string $expected): void
    {
        $text = new Text($raw);

        $this->assertInstanceOf(NodeInterface::class, $text);
        $this->assertSame($expected, $text->render());
        $this->assertSame($expected, (string) $text);
        $this->assertSame($raw, $text->getTextContent());
    }//end testTextIsEscaped()

    /**
     * Markup, and a best effort at the text in it.
     *
     * @return array
     */
    public static function rawHtml(): array
    {
        return [
            'markup'           => ['<b>bold</b> move', 'bold move'],
            'empty'            => ['', ''],
            'plain text'       => ['just text', 'just text'],
            'entities'         => ['Tom &amp; Jerry &lt;3', 'Tom & Jerry <3'],
            'apostrophe'       => ['it&apos;s &quot;ok&quot;', 'it\'s "ok"'],
            'nested'           => ['<ul><li>a</li><li>b</li></ul>', 'ab'],
            'void element'     => ['a<br>b<hr />c', 'abc'],
            'comment'          => ['a<!-- hidden -->b', 'ab'],
            'unclosed tag'     => ['<p>unclosed', 'unclosed'],
            'attributes'       => ['<a href="/x?a=1&amp;b=2" title="t">link</a>', 'link'],
            'multibyte'        => ['<i>日本語</i>', '日本語'],
        ];
    }//end rawHtml()

    /**
     * Tests that raw markup is output byte for byte.
     *
     * @param string $html The markup as given.
     * @param string $text The text we expect to find in it.
     *
     * @return void
     */
    #[DataProvider('rawHtml')]
    public function testRawHtmlIsUntouched(string $html, string $text): void
    {
        $raw = new RawHtml($html);

        $this->assertInstanceOf(NodeInterface::class, $raw);
        $this->assertSame($html, $raw->render());
        $this->assertSame($html, (string) $raw);
        $this->assertSame($text, $raw->getTextContent());
    }//end testRawHtmlIsUntouched()

    /**
     * Text that's fine inside a comment.
     *
     * @return array
     */
    public static function validComments(): array
    {
        return [
            'plain'                    => [' a comment ', '<!-- a comment -->'],
            'empty'                    => ['', '<!---->'],
            'markup'                   => ['<div>hidden</div>', '<!--<div>hidden</div>-->'],
            'single dashes'            => ['a - b - c', '<!--a - b - c-->'],
            'double dash in middle'    => ['a -- b', '<!--a -- b-->'],
            'ends with a dash'         => ['a-', '<!--a--->'],
            'starts with a dash'       => ['-a', '<!---a-->'],
            'greater than in middle'   => ['a > b', '<!--a > b-->'],
            'arrow in middle'          => ['a -> b', '<!--a -> b-->'],
            'unescaped ampersand'      => ['a & b', '<!--a & b-->'],
            'opener without bang'      => ['<-- a', '<!--<-- a-->'],
            'partial opener in middle' => ['a <!- b', '<!--a <!- b-->'],
            'conditional comment'      => ['[if IE]><p>x</p><![endif]', '<!--[if IE]><p>x</p><![endif]-->'],
            'multiline'                => ["\nline one\nline two\n", "<!--\nline one\nline two\n-->"],
            'multibyte'                => ['日本語', '<!--日本語-->'],
        ];
    }//end validComments()

    /**
     * Tests that comments are written verbatim between their markers.
     *
     * @param string $text     The text of the comment.
     * @param string $expected The comment as rendered.
     *
     * @return void
     */
    #[DataProvider('validComments')]
    public function testCommentsRender(string $text, string $expected): void
    {
        $comment = new Comment($text);

        $this->assertInstanceOf(NodeInterface::class, $comment);
        $this->assertSame($expected, $comment->render());
        $this->assertSame($expected, (string) $comment);
        $this->assertSame($text, $comment->getText());
        $this->assertSame('', $comment->getTextContent());
    }//end testCommentsRender()

    /**
     * Text that would end a comment early, or that the HTML syntax forbids in one.
     *
     * @return array
     */
    public static function invalidComments(): array
    {
        return [
            'starts with >'          => ['> a'],
            'only >'                 => ['>'],
            'starts with ->'         => ['-> a'],
            'only ->'                => ['->'],
            'contains opener'        => ['a <!-- b'],
            'starts with opener'     => ['<!-- a'],
            'contains closer'        => ['a --> b'],
            'ends with closer'       => ['a -->'],
            'is a closer'            => ['-->'],
            'contains bang closer'   => ['a --!> b'],
            'ends with <!-'          => ['a <!-'],
            'is <!-'                 => ['<!-'],
            'escape attempt'         => ['--><script>alert(1)</script><!--'],
            'bang escape attempt'    => ['--!><script>alert(1)</script>'],
        ];
    }//end invalidComments()

    /**
     * Tests that a comment can't be broken out of.
     *
     * @param string $text The text of the comment.
     *
     * @return void
     */
    #[DataProvider('invalidComments')]
    public function testRefusesUnsafeComments(string $text): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be written inside of an HTML comment');

        new Comment($text);
    }//end testRefusesUnsafeComments()

    /**
     * Tests the doctype.
     *
     * @return void
     */
    public function testDoctype(): void
    {
        $doctype = new Doctype();

        $this->assertInstanceOf(NodeInterface::class, $doctype);
        $this->assertSame('<!DOCTYPE html>', $doctype->render());
        $this->assertSame('<!DOCTYPE html>', (string) $doctype);
        $this->assertSame('', $doctype->getTextContent());
    }//end testDoctype()

    /**
     * Tests that nodes can be dropped straight into strings.
     *
     * @return void
     */
    public function testNodesAreStringable(): void
    {
        $this->assertInstanceOf(\Stringable::class, new Text('a'));
        $this->assertSame('1 &lt; 2 and <b>3</b>', new Text('1 < 2').' and '.new RawHtml('<b>3</b>'));
    }//end testNodesAreStringable()
}//end class
