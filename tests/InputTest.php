<?php
declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Elements\Input;
use PHPUnit\Framework\TestCase;

/**
 * @covers Input
 */
final class InputTest extends TestCase
{
    /**
     * The Input element.
     *
     * @var Input
     */
    public $el;

    public function setUp()
    {
        $this->el = new Input();
    }

    public function testRendersEmpty()
    {
        $this->assertEquals(
            '<input />',
            $this->el->render()
        );
    }
}


