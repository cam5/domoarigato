<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Pre;
use Cam5\Domoarigato\Elements\RawTextElement;
use Cam5\Domoarigato\Elements\Script;
use Cam5\Domoarigato\Elements\Style;
use Cam5\Domoarigato\Elements\Textarea;
use Cam5\Domoarigato\Elements\TextOnlyElement;
use Cam5\Domoarigato\Elements\Title;
use Cam5\Domoarigato\Elements\Traits\KeepsLeadingNewline;
use Cam5\Domoarigato\Nodes\Fragment;
use Cam5\Domoarigato\Nodes\RawHtml;
use Cam5\Domoarigato\Nodes\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the elements whose content a browser reads differently from everyone else's.
 */
#[CoversClass(RawTextElement::class)]
#[CoversClass(TextOnlyElement::class)]
#[CoversClass(Script::class)]
#[CoversClass(Style::class)]
#[CoversClass(Textarea::class)]
#[CoversClass(Title::class)]
#[CoversClass(Pre::class)]
#[CoversTrait(KeepsLeadingNewline::class)]
final class SpecialContentTest extends TestCase
{
    /**
     * Code that must come out of a raw text element exactly as it went in.
     *
     * @return array
     */
    public static function rawText(): array
    {
        return [
            'comparison'              => ['if (a < b && c > d) {}'],
            'string with quotes'      => ['var s = "it\'s";'],
            'entity lookalike'        => ['var s = "&amp; &lt;";'],
            'markup in a string'      => ['el.innerHTML = "<b>bold</b>";'],
            'the string "0"'          => ['0'],
            'whitespace'              => ["\n  a\n"],
            'multibyte'               => ['var s = "日本語";'],
            'css child selector'      => ['ul > li { content: "<"; }'],
            'closing tag of another'  => ['var s = "</div></style-ish></scripty>";'],
            'unfinished closing tag'  => ['var s = "</scrip";'],
            'bare closing slash'      => ['a </ b'],
            'html comment alone'      => ['<!-- old school -->'],
            'json'                    => ['{"a":"<\/script>"}'],
            'escaped opener'          => ['var s = "<\!--<\script>";'],
        ];
    }//end rawText()

    /**
     * Tests that scripts and styles are written verbatim, by every route in.
     *
     * @param string $code The code as given.
     *
     * @return void
     */
    #[DataProvider('rawText')]
    public function testRawTextIsNotEscaped(string $code): void
    {
        foreach (['script', 'style'] as $name) {
            $expected = '<'.$name.'>'.$code.'</'.$name.'>';

            $this->assertSame($expected, Domo::createElement($name)->setTextContent($code)->render());
            $this->assertSame($expected, Domo::createElement($name)->setText($code)->render());
            $this->assertSame($expected, Domo::createElement($name)->setInnerHtml($code)->render());
            $this->assertSame($expected, Domo::createElement($name)->appendChild($code)->render());
            $this->assertSame($expected, Domo::createElement($name)->prependChild(new Text($code))->render());
            $this->assertSame($expected, Domo::createElement($name)->append(new RawHtml($code))->render());
            $this->assertSame($expected, Domo::createElement($name, [], $code)->render());

            $el = Domo::createElement($name)->setText($code);

            $this->assertSame($code, $el->getTextContent());
            $this->assertSame($code, $el->getInnerHtml());
        }
    }//end testRawTextIsNotEscaped()

    /**
     * Code that would end a script early, however it's dressed up.
     *
     * @return array
     */
    public static function earlyEndings(): array
    {
        return [
            'closing tag'             => ['</%s>'],
            'in a string'             => ['var s = "</%s>";'],
            'with a space'            => ['</%s >'],
            'with a newline'          => ["</%s\n>"],
            'with a tab'              => ["</%s\t>"],
            'with a form feed'        => ["</%s\f>"],
            'with a carriage return'  => ["</%s\r>"],
            'with a slash'            => ['</%s/>'],
            'with attributes'         => ['</%s foo="bar">'],
            'unterminated, at end'    => ['x</%s'],
            'injection attempt'       => ['</%s><img src=x onerror=alert(1)>'],
        ];
    }//end earlyEndings()

    /**
     * Tests that content which would close the element is refused, in any letter case.
     *
     * @param string $format The offending code, with a placeholder for the tag name.
     *
     * @return void
     */
    #[DataProvider('earlyEndings')]
    public function testRefusesContentThatEndsTheElement(string $format): void
    {
        foreach (['script', 'style'] as $name) {
            foreach ([$name, strtoupper($name), ucfirst($name)] as $spelling) {
                $el = Domo::createElement($name)->setText('kept');

                try {
                    $el->setText(sprintf($format, $spelling));
                    $this->fail('Content that ends the element was accepted.');
                } catch (\InvalidArgumentException $e) {
                    $this->assertStringContainsString('would end the element early', $e->getMessage());
                    $this->assertSame('<'.$name.'>kept</'.$name.'>', $el->render());
                }
            }
        }
    }//end testRefusesContentThatEndsTheElement()

    /**
     * Tests that one element's closing tag is no trouble inside of the other.
     *
     * @return void
     */
    public function testOtherElementsClosingTagsAreFine(): void
    {
        $this->assertSame(
            '<style>/* </script> */</style>',
            Domo::createElement('style')->setText('/* </script> */')->render()
        );
        $this->assertSame(
            '<script>// </style></script>',
            Domo::createElement('script')->setText('// </style>')->render()
        );
    }//end testOtherElementsClosingTagsAreFine()

    /**
     * Tests that a closing tag can't be assembled out of harmless pieces.
     *
     * @return void
     */
    public function testRefusesClosingTagsBuiltFromPieces(): void
    {
        $attempts = [
            'appending'  => fn ($el) => $el->appendChild('</scr')->appendChild('ipt>'),
            'prepending' => fn ($el) => $el->appendChild('ipt>')->prependChild('</scr'),
            'variadic'   => fn ($el) => $el->append('<', '/script', '>'),
            'mixed kind' => fn ($el) => $el->append(new Text('</script'), new RawHtml(' >')),
            'raw markup' => fn ($el) => $el->appendChild('ok')->setInnerHtml('</script>'),
        ];

        foreach ($attempts as $how => $attempt) {
            $el = new Script();

            try {
                $attempt($el);
                $this->fail('A closing tag was built by '.$how.'.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringNotContainsStringIgnoringCase('</script', $el->getInnerHtml());
            }
        }
    }//end testRefusesClosingTagsBuiltFromPieces()

    /**
     * Tests that a refused change is undone completely.
     *
     * @return void
     */
    public function testRefusedChangesAreRolledBack(): void
    {
        $el = (new Script())->append('a();', 'b();');

        try {
            $el->append('c();', '</script>', 'd();');
            $this->fail('A closing tag was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('<script>a();b();</script>', $el->render());
            $this->assertCount(2, $el->getChildren());
        }

        try {
            $el->prependChild('</script>');
            $this->fail('A closing tag was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('<script>a();b();</script>', $el->render());
        }
    }//end testRefusedChangesAreRolledBack()

    /**
     * Code that would leave a script open for the rest of the page.
     *
     * @return array
     */
    public static function unendingScripts(): array
    {
        return [
            'comment then script'        => ['<!--<script>'],
            'in a string'                => ['var s = "<!--<script>";'],
            'uppercase'                  => ['<!-- <SCRIPT>'],
            'with attributes'            => ['<!--<script src="x">'],
            'script before the comment'  => ['<script> <!--'],
            'unterminated, at end'       => ['<!-- <script'],
            'even with a comment closer' => ['<!--<script>-->'],
        ];
    }//end unendingScripts()

    /**
     * Tests that scripts refuse the combination that double-escapes them.
     *
     * @param string $code The offending code.
     *
     * @return void
     */
    #[DataProvider('unendingScripts')]
    public function testScriptRefusesContentThatNeverEnds(string $code): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('would keep the element from ending');

        Domo::createElement('script')->setText($code);
    }//end testScriptRefusesContentThatNeverEnds()

    /**
     * Tests that each half of that combination is fine alone, and that styles don't care.
     *
     * @return void
     */
    public function testScriptAllowsEachHalfAlone(): void
    {
        foreach (['<!-- a comment -->', 'var s = "<script>";', '<scripts>', '<!-<script>'] as $code) {
            $this->assertSame('<script>'.$code.'</script>', Domo::createElement('script')->setText($code)->render());
        }

        $this->assertSame(
            '<style><!--<script></style>',
            Domo::createElement('style')->setText('<!--<script>')->render()
        );
    }//end testScriptAllowsEachHalfAlone()

    /**
     * Tests that raw text elements take text, and only text.
     *
     * @return void
     */
    public function testRawTextElementsRefuseOtherNodes(): void
    {
        $nodes = [
            'an element'  => Domo::createElement('b'),
            'a comment'   => Domo::comment('x'),
            'a doctype'   => Domo::doctype(),
            'a fragment'  => new Fragment('x'),
            'itself'      => null,
        ];

        foreach (['script', 'style'] as $name) {
            foreach ($nodes as $what => $node) {
                $el = Domo::createElement($name)->setText('kept');

                try {
                    $el->appendChild(($node ?? $el));
                    $this->fail('A '.$name.' accepted '.$what.'.');
                } catch (\InvalidArgumentException $e) {
                    $this->assertSame('A "'.$name.'" element can only contain text.', $e->getMessage());
                    $this->assertSame('kept', $el->getTextContent());
                }
            }
        }
    }//end testRawTextElementsRefuseOtherNodes()

    /**
     * Tests the rest of a raw text element's behaviour.
     *
     * @return void
     */
    public function testRawTextElementBasics(): void
    {
        $el = new Style();

        $this->assertSame($el, $el->setText('a{}'));
        $this->assertSame($el, $el->appendChild('b{}'));
        $this->assertSame($el, $el->prependChild('@charset "utf-8";'));
        $this->assertSame($el, $el->append());
        $this->assertSame($el, $el->append(1, 2.5));
        $this->assertSame('<style>@charset "utf-8";a{}b{}12.5</style>', $el->render());

        $this->assertSame('<style></style>', $el->setText('')->render());
        $this->assertFalse($el->hasChildren());
        $this->assertSame('<style></style>', $el->setInnerHtml('')->render());

        $script = Domo::createElement('script', ['type' => 'application/json', 'id' => 'data'], '{"a":"1 < 2"}');

        $this->assertSame('<script type="application/json" id="data">{"a":"1 < 2"}</script>', $script->render());

        $copy = clone $script;
        $copy->setText('{}');

        $this->assertSame('{"a":"1 < 2"}', $script->getTextContent());
    }//end testRawTextElementBasics()

    /**
     * Tests embedding JSON, which is where hostile strings usually arrive from.
     *
     * @return void
     */
    public function testEmbeddingJson(): void
    {
        // json_encode escapes "/" by default, which takes care of a closing tag.
        $data   = ['html' => '</script><script>alert(1)</script>'];
        $script = Domo::createElement('script', ['type' => 'application/json'])->setText(json_encode($data));

        $this->assertSame($data, json_decode($script->getTextContent(), true));
        $this->assertSame(1, substr_count($script->render(), '</script>'));

        // It does not take care of "<!--<script>". JSON_HEX_TAG does, and is the flag to reach for.
        $data = ['html' => '<!--<script>'];

        try {
            Domo::createElement('script')->setText(json_encode($data));
            $this->fail('JSON that leaves the script open was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('would keep the element from ending', $e->getMessage());
        }

        $script = Domo::createElement('script')->setText(json_encode($data, JSON_HEX_TAG));

        $this->assertStringNotContainsString('<!--', $script->getTextContent());
        $this->assertStringNotContainsString('<script', $script->getTextContent());
        $this->assertStringContainsString('003C!--', $script->render());
        $this->assertSame($data, json_decode($script->getTextContent(), true));

        // Without the default slash escaping, the closing tag is caught as well.
        $this->expectException(\InvalidArgumentException::class);

        Domo::createElement('script')->setText(json_encode(['html' => '</script>'], JSON_UNESCAPED_SLASHES));
    }//end testEmbeddingJson()

    /**
     * Text that <title> and <textarea> must escape, since they can't hold markup.
     *
     * @return array
     */
    public static function textOnly(): array
    {
        return [
            'plain'             => ['Hello', 'Hello'],
            'markup'            => ['<b>bold</b>', '&lt;b&gt;bold&lt;/b&gt;'],
            'own closing tag'   => ['</%1$s>', '&lt;/%1$s&gt;'],
            'breakout attempt'  => ['</%1$s><script>alert(1)</script>', '&lt;/%1$s&gt;&lt;script&gt;alert(1)&lt;/script&gt;'],
            'ampersand'         => ['Tom & Jerry', 'Tom &amp; Jerry'],
            'entity lookalike'  => ['&lt;', '&amp;lt;'],
            'quotes'            => ['"a"', '"a"'],
            'the string "0"'    => ['0', '0'],
            'multibyte'         => ['日本語', '日本語'],
        ];
    }//end textOnly()

    /**
     * Tests that <title> and <textarea> escape their text, and return it as given.
     *
     * @param string $text     The text as given, with a placeholder for the tag name.
     * @param string $expected The text as rendered.
     *
     * @return void
     */
    #[DataProvider('textOnly')]
    public function testTextOnlyElementsEscape(string $text, string $expected): void
    {
        foreach (['title', 'textarea'] as $name) {
            $given = sprintf($text, $name);
            $el    = Domo::createElement($name)->setText($given);

            $this->assertSame('<'.$name.'>'.sprintf($expected, $name).'</'.$name.'>', $el->render());
            $this->assertSame($given, $el->getTextContent());
            $this->assertSame(1, substr_count($el->render(), '</'.$name.'>'));
        }
    }//end testTextOnlyElementsEscape()

    /**
     * Tests that <title> and <textarea> take text, and only text.
     *
     * @return void
     */
    public function testTextOnlyElementsRefuseOtherNodes(): void
    {
        $attempts = [
            'an element'     => fn ($el) => $el->appendChild(Domo::createElement('b')),
            'a prepended el' => fn ($el) => $el->prependChild(Domo::createElement('b')),
            'a comment'      => fn ($el) => $el->append('a', Domo::comment('x')),
            'a fragment'     => fn ($el) => $el->appendChild(new Fragment('x')),
            'raw markup'     => fn ($el) => $el->appendChild(Domo::raw('<b>x</b>')),
            'inner html'     => fn ($el) => $el->setInnerHtml('<b>x</b>'),
            'itself'         => fn ($el) => $el->appendChild($el),
        ];

        foreach (['title', 'textarea'] as $name) {
            foreach ($attempts as $what => $attempt) {
                $el = Domo::createElement($name)->setText('kept');

                try {
                    $attempt($el);
                    $this->fail('A '.$name.' accepted '.$what.'.');
                } catch (\InvalidArgumentException $e) {
                    $this->assertSame('A "'.$name.'" element can only contain text.', $e->getMessage());
                    $this->assertSame('<'.$name.'>kept</'.$name.'>', $el->render());
                }
            }

            try {
                Domo::createElement($name, [], [Domo::createElement('b')]);
                $this->fail('A '.$name.' was created around an element.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('can only contain text', $e->getMessage());
            }
        }
    }//end testTextOnlyElementsRefuseOtherNodes()

    /**
     * Tests the ways text does get into a text-only element.
     *
     * @return void
     */
    public function testTextOnlyElementsTakeTextEveryWay(): void
    {
        $el = new Textarea();

        $el->appendChild('b')
            ->prependChild(new Text('a'))
            ->append('c', 1, 2.5);

        $this->assertSame('<textarea>abc12.5</textarea>', $el->render());
        $this->assertSame('<textarea></textarea>', $el->setInnerHtml('')->render());
        $this->assertSame('<title>a &amp; b</title>', (string) Domo::createElement('title', [], 'a & b'));
        $this->assertSame(
            '<textarea name="bio" rows="3" required>Hi</textarea>',
            Domo::createElement('textarea', ['name' => 'bio', 'rows' => 3, 'required' => true], 'Hi')->render()
        );
    }//end testTextOnlyElementsTakeTextEveryWay()

    /**
     * Content for <pre> and <textarea>, and what has to be written for a browser to read it back the same.
     *
     * @return array
     */
    public static function leadingNewlines(): array
    {
        return [
            'no newline'              => ['text', 'text'],
            'leading newline'         => ["\ntext", "\n\ntext"],
            'two leading newlines'    => ["\n\ntext", "\n\n\ntext"],
            'only a newline'          => ["\n", "\n\n"],
            'leading crlf'            => ["\r\ntext", "\n\r\ntext"],
            'leading cr'              => ["\rtext", "\n\rtext"],
            'newline later on'        => ["a\nb", "a\nb"],
            'trailing newline'        => ["text\n", "text\n"],
            'leading space then nl'   => [" \ntext", " \ntext"],
            'leading tab'             => ["\ttext", "\ttext"],
            'empty'                   => ['', ''],
        ];
    }//end leadingNewlines()

    /**
     * Tests that a leading newline survives the trip through a browser's parser.
     *
     * @param string $content  The content as given.
     * @param string $expected The content as rendered.
     *
     * @return void
     */
    #[DataProvider('leadingNewlines')]
    public function testLeadingNewlinesAreKept(string $content, string $expected): void
    {
        foreach (['pre', 'textarea'] as $name) {
            $el = Domo::createElement($name)->setText($content);

            $this->assertSame('<'.$name.'>'.$expected.'</'.$name.'>', $el->render());
            $this->assertSame($content, $el->getTextContent());
            $this->assertSame($content, $el->getInnerHtml());
        }

        // Nobody else needs the extra newline.
        $this->assertSame('<div>'.$content.'</div>', Domo::createElement('div')->setText($content)->render());
        $this->assertSame('<title>'.$content.'</title>', Domo::createElement('title')->setText($content)->render());
        $this->assertSame('<script>'.$content.'</script>', Domo::createElement('script')->setText($content)->render());
    }//end testLeadingNewlinesAreKept()

    /**
     * Tests the newline rule on a <pre> that starts with markup or with an element.
     *
     * @return void
     */
    public function testLeadingNewlineInsideAPre(): void
    {
        $this->assertSame("<pre>\n\n<b>x</b></pre>", Domo::createElement('pre')->setInnerHtml("\n<b>x</b>")->render());

        // The newline is inside the <code>, not straight after the <pre>.
        $pre = Domo::createElement('pre', [], Domo::createElement('code', [], "\nline"));

        $this->assertSame("<pre><code>\nline</code></pre>", $pre->render());

        $pre->prependChild("\n");

        $this->assertSame("<pre>\n\n<code>\nline</code></pre>", $pre->render());
    }//end testLeadingNewlineInsideAPre()
}//end class
