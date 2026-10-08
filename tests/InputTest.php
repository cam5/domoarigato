<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Elements\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Cam5\Domoarigato\Elements\Input::class)]
final class InputTest extends TestCase
{
    /**
     * The Input element.
     *
     * @var Input
     */
    public $el;

    protected function setUp(): void
    {
        $this->el = new Input();
    }

    public function testRendersEmpty(): void
    {
        $this->assertEquals(
            '<input />',
            $this->el->render()
        );
    }
}


