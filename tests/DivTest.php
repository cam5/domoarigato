<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Elements\Div;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Cam5\Domoarigato\Elements\Div::class)]
#[CoversClass(\Cam5\Domoarigato\Factories\AttributeFactory::class)]
#[CoversTrait(\Cam5\Domoarigato\Elements\Traits\BaseElement::class)]
final class DivTest extends TestCase
{
    /**
     * The Div element.
     *
     * @var Div
     */
    public $div;

    protected function setUp(): void
    {
        $this->div = new Div();
    }

    public function testRendersEmpty(): void
    {
        $this->assertEquals(
            '<div></div>',
            $this->div->render()
        );
    }

    public function testRendersContentString(): void
    {
        $this->div->setTextContent('Hello World');

        $this->assertEquals(
            '<div>Hello World</div>',
            $this->div->render()
        );
    }

    public function testAddingAttributes(): void
    {
        $this->div->addAttribute('id', 'lorem');

        $this->assertEquals(
            '<div id="lorem"></div>',
            $this->div->render()
        );
    }

    public function testTagName(): void
    {
        $this->assertEquals(
            'div',
            $this->div->getTagName()
        );
    }
}


