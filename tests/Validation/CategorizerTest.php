<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\ElementInterface;
use Cam5\Domoarigato\Elements\SelfEnclosingElement;
use Cam5\Domoarigato\Enums\Categories;
use Cam5\Domoarigato\Enums\Elements;
use Cam5\Domoarigato\Validation\Categorizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests working out the categories of actual elements.
 */
#[CoversClass(Categorizer::class)]
final class CategorizerTest extends TestCase
{
    /**
     * Elements whose categories depend on their attributes or children.
     *
     * Each case: tag name, attributes, children, then the categories gained when the condition is met.
     *
     * @return array
     */
    public static function conditions(): array
    {
        $li = fn () => [Domo::createElement('li')];

        return [
            'a without href'              => ['a', [], [], []],
            'a with href'                 => ['a', ['href' => '/'], [], ['interactive']],
            'a with empty href'           => ['a', ['href' => ''], [], ['interactive']],
            'a with href switched off'    => ['a', ['href' => null], [], []],
            'audio without controls'      => ['audio', [], [], []],
            'audio with controls'         => ['audio', ['controls' => true], [], ['interactive', 'palpable']],
            'audio with controls off'     => ['audio', ['controls' => false], [], []],
            'video without controls'      => ['video', ['src' => 'a.mp4'], [], []],
            'video with controls'         => ['video', ['controls' => true], [], ['interactive']],
            'img without usemap'          => ['img', ['src' => 'a.png'], [], []],
            'img with usemap'             => ['img', ['usemap' => '#m'], [], ['interactive']],
            'input without type'          => ['input', [], [], ['interactive', 'palpable']],
            'input type text'             => ['input', ['type' => 'text'], [], ['interactive', 'palpable']],
            'input type hidden'           => ['input', ['type' => 'hidden'], [], []],
            'input type HIDDEN'           => ['input', ['type' => 'HIDDEN'], [], []],
            'input type switched off'     => ['input', ['type' => null], [], ['interactive', 'palpable']],
            'input with a bare type'      => ['input', ['type' => true], [], ['interactive', 'palpable']],
            'input type hidden-ish'       => ['input', ['type' => 'hiddenx'], [], ['interactive', 'palpable']],
            'meta without itemprop'       => ['meta', ['charset' => 'utf-8'], [], []],
            'meta with itemprop'          => ['meta', ['itemprop' => 'name'], [], ['flow', 'phrasing']],
            'link without rel'            => ['link', ['href' => 'a.css'], [], []],
            'link with itemprop'          => ['link', ['itemprop' => 'url'], [], ['flow', 'phrasing']],
            'link rel stylesheet'         => ['link', ['rel' => 'stylesheet'], [], ['flow', 'phrasing']],
            'link rel STYLESHEET'         => ['link', ['rel' => 'STYLESHEET'], [], ['flow', 'phrasing']],
            'link rel preload prefetch'   => ['link', ['rel' => 'preload prefetch'], [], ['flow', 'phrasing']],
            'link rel icon'               => ['link', ['rel' => 'icon'], [], []],
            'link rel stylesheet + icon'  => ['link', ['rel' => 'stylesheet icon'], [], []],
            'link rel icon + itemprop'    => ['link', ['rel' => 'icon', 'itemprop' => 'x'], [], ['flow', 'phrasing']],
            'link with an empty rel'      => ['link', ['rel' => ''], [], []],
            'ul without items'            => ['ul', [], [], []],
            'ul with an item'             => ['ul', [], $li, ['palpable']],
            'ul with only text'           => ['ul', [], ['text'], []],
            'ul with only a script'       => ['ul', [], fn () => [Domo::createElement('script')], []],
            'ol with an item'             => ['ol', [], $li, ['palpable']],
            'ol without items'            => ['ol', [], [], []],
            'menu with an item'           => ['menu', [], $li, ['palpable']],
            'menu without items'          => ['menu', [], [], []],
            'dl without groups'           => ['dl', [], [], []],
            'dl with a term'              => ['dl', [], fn () => [Domo::createElement('dt')], ['palpable']],
            'dl with a description'       => ['dl', [], fn () => [Domo::createElement('dd')], ['palpable']],
            'dl with a div'               => ['dl', [], fn () => ['text', Domo::createElement('div')], ['palpable']],
            'dl with a list item'         => ['dl', [], $li, []],
            'area'                        => ['area', [], [], ['flow', 'phrasing']],
            'main'                        => ['main', [], [], ['flow']],
        ];
    }//end conditions()

    /**
     * Tests the categories that come and go with an element's attributes and children.
     *
     * @param string         $name       The tag name.
     * @param array          $attributes The attributes to give it.
     * @param array|callable $children   The children to give it, or something that makes them.
     * @param string[]       $gained     The conditional categories we expect it to be in.
     *
     * @return void
     */
    #[DataProvider('conditions')]
    public function testConditionalCategories(string $name, array $attributes, array|callable $children, array $gained): void
    {
        $children = (true === is_callable($children)) ? $children() : $children;
        $element  = Domo::createElement($name, $attributes, $children);

        $this->assertSame(array_merge(Categories::of($name), $gained), Categorizer::categoriesOf($element));
    }//end testConditionalCategories()

    /**
     * Tests that elements with nothing conditional about them get exactly what the enum says.
     *
     * @return void
     */
    public function testUnconditionalCategories(): void
    {
        foreach (array_keys(Elements::all()) as $name) {
            if ([] === Categories::conditionallyOf($name)) {
                $this->assertSame(Categories::of($name), Categorizer::categoriesOf(Domo::createElement($name)), $name);
            }
        }

        $this->assertSame([], Categorizer::categoriesOf(Domo::createElement('li')));
        $this->assertSame(['flow', 'phrasing', 'palpable'], Categorizer::categoriesOf(Domo::createElement('em')));
    }//end testUnconditionalCategories()

    /**
     * Tests that elements from outside of HTML are treated as custom elements.
     *
     * @return void
     */
    public function testUnknownElementsAreCustomElements(): void
    {
        foreach (['my-element', 'center', 'circle'] as $name) {
            $this->assertSame(
                ['flow', 'phrasing', 'palpable'],
                Categorizer::categoriesOf(Domo::createElement($name)),
                $name
            );
        }
    }//end testUnknownElementsAreCustomElements()

    /**
     * Tests that an element which can't have children is never said to have the right ones.
     *
     * @return void
     */
    public function testAListThatCannotHoldItemsIsNotPalpable(): void
    {
        $oddity = new class extends SelfEnclosingElement implements ElementInterface {
            /**
             * Claims to be a list.
             *
             * @return string
             */
            public function getTagName(): string
            {
                return 'ul';
            }//end getTagName()
        };

        $this->assertSame(['flow'], Categorizer::categoriesOf($oddity));
    }//end testAListThatCannotHoldItemsIsNotPalpable()

    /**
     * Tests that the list of "rel" keywords allowed in the body is the spec's.
     *
     * @return void
     */
    public function testBodyOkKeywords(): void
    {
        $this->assertSame(
            ['dns-prefetch', 'modulepreload', 'pingback', 'preconnect', 'prefetch', 'preload', 'stylesheet'],
            Categorizer::BODY_OK
        );

        foreach (Categorizer::BODY_OK as $keyword) {
            $link = Domo::createElement('link', ['rel' => $keyword]);

            $this->assertContains('flow', Categorizer::categoriesOf($link), $keyword);
        }
    }//end testBodyOkKeywords()
}//end class
