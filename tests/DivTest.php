<?php
declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Elements\Div;
use PHPUnit\Framework\TestCase;

/**
 * @covers Div
 */
final class DivTest extends TestCase
{
    /**
     * The Div element.
     *
     * @var Div
     */
    public $div;

    public function setUp()
    {
        $this->div = new Div();
    }

    public function testRendersEmpty()
    {
        $this->assertEquals(
            '<div></div>',
            $this->div->render()
        );
    }

    public function testRendersContentString()
    {
        $this->div->setTextContent('Hello World');

        $this->assertEquals(
            '<div>Hello World</div>',
            $this->div->render()
        );
    }

    public function testAddingAttributes()
    {
        $this->div->addAttribute('id', 'lorem');

        $this->assertEquals(
            '<div id="lorem"></div>',
            $this->div->render()
        );
    }
}


