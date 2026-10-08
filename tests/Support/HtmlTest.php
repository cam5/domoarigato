<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Support\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the escaping and validation helpers.
 */
#[CoversClass(Html::class)]
final class HtmlTest extends TestCase
{
    /**
     * Raw attribute values, and what they should look like between double quotes.
     *
     * @return array
     */
    public static function attributeValues(): array
    {
        return [
            'plain text'           => ['lorem ipsum', 'lorem ipsum'],
            'empty string'         => ['', ''],
            'double quote'         => ['say "hi"', 'say &quot;hi&quot;'],
            'single quote'         => ["it's", 'it&apos;s'],
            'ampersand'            => ['a&b', 'a&amp;b'],
            'angle brackets'       => ['<b>', '&lt;b&gt;'],
            'existing entity'      => ['&amp;', '&amp;amp;'],
            'breakout attempt'     => ['" onclick="alert(1)', '&quot; onclick=&quot;alert(1)'],
            'multibyte'            => ['naïve ☃ 日本語', 'naïve ☃ 日本語'],
            'newlines and tabs'    => ["a\n\tb", "a\n\tb"],
            'invalid utf-8'        => ["a\xFFb", "a\u{FFFD}b"],
            'the string "0"'       => ['0', '0'],
            'url with query'       => ['/?a=1&b=2', '/?a=1&amp;b=2'],
        ];
    }//end attributeValues()

    /**
     * Test that attribute values can't break out of their quotes.
     *
     * @param string $raw      The value as given.
     * @param string $expected The value as it should be written.
     *
     * @return void
     */
    #[DataProvider('attributeValues')]
    public function testEscapesAttributeValues(string $raw, string $expected): void
    {
        $this->assertSame($expected, Html::escapeAttribute($raw));
    }//end testEscapesAttributeValues()

    /**
     * Names we accept, and the form we store them in.
     *
     * @return array
     */
    public static function validAttributeNames(): array
    {
        return [
            'lowercase'           => ['id', 'id'],
            'uppercase'           => ['ID', 'id'],
            'mixed case'          => ['tabIndex', 'tabindex'],
            'data attribute'      => ['data-user-id', 'data-user-id'],
            'aria attribute'      => ['aria-labelledby', 'aria-labelledby'],
            'namespaced'          => ['xlink:href', 'xlink:href'],
            'framework shorthand' => ['@click', '@click'],
            'framework binding'   => [':class', ':class'],
            'with a dot'          => ['x-on:click.prevent', 'x-on:click.prevent'],
            'with underscore'     => ['_private', '_private'],
            'digits'              => ['h1', 'h1'],
            'only digits'         => ['42', '42'],
            'non-ascii letter'    => ['données', 'données'],
            'non-ascii uppercase' => ['Ünïcode', 'Ünïcode'],
            'emoji'               => ["\u{1F600}", "\u{1F600}"],
            'astral, not a nonchar' => ["\u{2FFFD}", "\u{2FFFD}"],
        ];
    }//end validAttributeNames()

    /**
     * Test that valid names pass, and that only ASCII letters are lowercased.
     *
     * @param string $name     The name as given.
     * @param string $expected The name as stored.
     *
     * @return void
     */
    #[DataProvider('validAttributeNames')]
    public function testNormalizesAttributeNames(string $name, string $expected): void
    {
        $this->assertSame($expected, Html::normalizeAttributeName($name));
    }//end testNormalizesAttributeNames()

    /**
     * Names that would produce broken, or dangerous, markup.
     *
     * @return array
     */
    public static function invalidAttributeNames(): array
    {
        return [
            'empty'                     => [''],
            'space'                     => ['a b'],
            'only a space'              => [' '],
            'leading space'             => [' id'],
            'trailing space'            => ['id '],
            'tab'                       => ["a\tb"],
            'line feed'                 => ["a\nb"],
            'form feed'                 => ["a\fb"],
            'carriage return'           => ["a\rb"],
            'null byte'                 => ["a\0b"],
            'c0 control'                => ["a\x1Fb"],
            'delete'                    => ["a\x7Fb"],
            'c1 control'                => ["a\u{80}b"],
            'last c1 control'           => ["a\u{9F}b"],
            'double quote'              => ['a"b'],
            'single quote'              => ["a'b"],
            'greater than'              => ['a>b'],
            'less than'                 => ['a<b'],
            'solidus'                   => ['a/b'],
            'equals'                    => ['a=b'],
            'injection attempt'         => ['id="x" onclick'],
            'tag close attempt'         => ['x><script'],
            'first bmp nonchar range'   => ["\u{FDD0}"],
            'last bmp nonchar range'    => ["\u{FDEF}"],
            'U+FFFE'                    => ["\u{FFFE}"],
            'U+FFFF'                    => ["\u{FFFF}"],
            'U+1FFFE'                   => ["\u{1FFFE}"],
            'U+8FFFF'                   => ["\u{8FFFF}"],
            'U+10FFFE'                  => ["\u{10FFFE}"],
            'U+10FFFF'                  => ["\u{10FFFF}"],
            'invalid utf-8'             => ["a\xFF"],
            'truncated utf-8 sequence'  => ["\xE2\x82"],
        ];
    }//end invalidAttributeNames()

    /**
     * Test that invalid names are refused.
     *
     * @param string $name The name as given.
     *
     * @return void
     */
    #[DataProvider('invalidAttributeNames')]
    public function testRefusesInvalidAttributeNames(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Html::normalizeAttributeName($name);
    }//end testRefusesInvalidAttributeNames()

    /**
     * Test that every noncharacter in every plane is refused, and its neighbours aren't.
     *
     * @return void
     */
    public function testRefusesNoncharactersInEveryPlane(): void
    {
        for ($plane = 0; $plane <= 16; $plane++) {
            foreach ([0xFFFE, 0xFFFF] as $offset) {
                $char = mb_chr((($plane * 0x10000) + $offset), 'UTF-8');

                try {
                    Html::normalizeAttributeName('a'.$char);
                    $this->fail(sprintf('U+%X was accepted.', (($plane * 0x10000) + $offset)));
                } catch (\InvalidArgumentException $e) {
                    $this->assertStringContainsString('not a valid attribute name', $e->getMessage());
                }
            }

            $neighbour = mb_chr((($plane * 0x10000) + 0xFFFD), 'UTF-8');

            $this->assertSame('a'.$neighbour, Html::normalizeAttributeName('a'.$neighbour));
        }
    }//end testRefusesNoncharactersInEveryPlane()

    /**
     * Test that the two failure modes can be told apart.
     *
     * @return void
     */
    public function testExceptionMessages(): void
    {
        try {
            Html::normalizeAttributeName('');
            $this->fail('An empty name was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('An attribute name cannot be empty.', $e->getMessage());
        }

        try {
            Html::normalizeAttributeName('a b');
            $this->fail('A name with a space was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('"a b" is not a valid attribute name.', $e->getMessage());
        }
    }//end testExceptionMessages()
}//end class
