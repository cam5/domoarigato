<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Div;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Elements\ElementInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Cam5\Domoarigato\Domo::class)]
#[CoversClass(\Cam5\Domoarigato\Factories\ElementFactory::class)]
final class DomoTest extends TestCase
{
    public function testCreatesADivByName(): void
    {
        $this->assertInstanceOf(
            Div::class,
            Domo::createElement('div')
        );
    }

    public function testCreatesGenericElementByName(): void
    {
        $this->assertInstanceOf(
            GenericElement::class,
            Domo::createElement('test-element')
        );
    }

    public function testCreatesElementsConformingToACommonInterface(): void
    {
        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('any-old-thing')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('input')
        );

        $this->assertInstanceOf(
            ElementInterface::class,
            Domo::createElement('div')
        );
    }
}
