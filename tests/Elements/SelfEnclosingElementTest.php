<?php

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Input;
use PHPUnit\Framework\TestCase;

/**
 * Test elements descended from the abstract `SelfEnclosingElement`
 *
 * @covers Cam5\Domoarigato\Elements\EnclosingElement
 */
final class SelfEnclosingElementTest extends TestCase
{

    /**
     * An element object with methods we'll be testing.
     *
     * @var EnclosingElement
     */
    public $el;

    /**
     * PHPUnit convenience method run before each test* method.
     *
     * @return void
     */
    public function setUp()
    {
        // We know that 'input' is self-enclosing.
        $this->el = Domo::createElement('input');
    }//end setUp()

    /**
     * Render the text from the above test.
     *
     * @return void
     */
    public function testRendering()
    {
        $this->assertEquals(
            $this->el->render(),
            '<input />'
        );
    }//end testRendering()
}//end class

