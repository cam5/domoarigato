<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Attributes\OnOffAttribute;
use Cam5\Domoarigato\Attributes\SimpleAttribute;
use Cam5\Domoarigato\Attributes\TrueFalseAttribute;
use Cam5\Domoarigato\Attributes\YesNoAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the attributes that spell out their boolean states.
 */
#[CoversClass(TrueFalseAttribute::class)]
#[CoversClass(YesNoAttribute::class)]
#[CoversClass(OnOffAttribute::class)]
final class TrueFalseAttributeTest extends TestCase
{
    /**
     * Each class, with the keywords it writes for true and false.
     *
     * @return array
     */
    public static function keywords(): array
    {
        return [
            'true/false' => [TrueFalseAttribute::class, 'true', 'false'],
            'yes/no'     => [YesNoAttribute::class, 'yes', 'no'],
            'on/off'     => [OnOffAttribute::class, 'on', 'off'],
        ];
    }//end keywords()

    /**
     * Tests that booleans are written as keywords, rather than by presence.
     *
     * @param string $class The attribute class.
     * @param string $on    The keyword for true.
     * @param string $off   The keyword for false.
     *
     * @return void
     */
    #[DataProvider('keywords')]
    public function testBooleansBecomeKeywords(string $class, string $on, string $off): void
    {
        $attr = new $class();
        $attr->setKey('foo');

        $this->assertInstanceOf(SimpleAttribute::class, $attr);

        $this->assertSame($attr, $attr->setValue(true));
        $this->assertSame($on, $attr->getValue());
        $this->assertSame('foo="'.$on.'"', $attr->render());

        $attr->setValue(false);

        $this->assertSame($off, $attr->getValue());
        $this->assertFalse($attr->isEmpty());
        $this->assertSame('foo="'.$off.'"', $attr->render());
    }//end testBooleansBecomeKeywords()

    /**
     * Tests that everything other than a boolean behaves as it does on a simple attribute.
     *
     * @param string $class The attribute class.
     *
     * @return void
     */
    #[DataProvider('keywords')]
    public function testOtherValuesPassThrough(string $class): void
    {
        $attr = new $class();
        $attr->setKey('foo');

        $this->assertTrue($attr->isEmpty());
        $this->assertSame('foo="plaintext-only"', $attr->setValue('plaintext-only')->render());
        $this->assertSame('foo=""', $attr->setValue('')->render());
        $this->assertSame('foo="false"', $attr->setValue('false')->render());
        $this->assertSame('foo="1"', $attr->setValue(1)->render());
        $this->assertSame('foo="&quot;"', $attr->setValue('"')->render());
        $this->assertSame('', $attr->setValue(null)->render());
        $this->assertTrue($attr->isEmpty());

        $this->expectException(\InvalidArgumentException::class);

        $attr->setValue([true]);
    }//end testOtherValuesPassThrough()
}//end class
