<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test elements descended from the abstract `SelfEnclosingElement`
 */
#[CoversClass(\Cam5\Domoarigato\Elements\SelfEnclosingElement::class)]
final class SelfEnclosingElementTest extends TestCase
{

    /**
     * An element object with methods we'll be testing.
     *
     * @var SelfEnclosingElement
     */
    public $el;

    /**
     * PHPUnit convenience method run before each test* method.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // We know that 'input' is self-enclosing.
        $this->el = Domo::createElement('input');
    }//end setUp()

    /**
     * Render the text from the above test.
     *
     * @return void
     */
    public function testRendering(): void
    {
        $this->assertEquals(
            $this->el->render(),
            '<input />'
        );
    }//end testRendering()
}//end class

