<?php

declare(strict_types=1);

namespace Cam5\Domoarigato\tests;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Runs the examples in the README, so that it can't promise things the code doesn't do.
 *
 * Every "php" code block must be followed by an "html" block holding its exact output.
 */
#[CoversNothing]
final class ReadmeTest extends TestCase
{
    /**
     * Pairs every example in the README with the output it claims.
     *
     * @return array
     */
    public static function examples(): array
    {
        $readme = file_get_contents(dirname(__DIR__).'/README.md');

        preg_match_all('/^```php\n(.*?)^```\n\s*^```html\n(.*?)^```$/ms', $readme, $matches, PREG_SET_ORDER);

        $examples = [];

        foreach ($matches as $index => $match) {
            $examples['example #'.($index + 1)] = [$match[1], $match[2]];
        }

        return $examples;
    }//end examples()

    /**
     * Tests that there are examples, and that none of them lost its expected output.
     *
     * @return void
     */
    public function testEveryExampleHasItsOutput(): void
    {
        $readme = file_get_contents(dirname(__DIR__).'/README.md');

        $this->assertNotEmpty(self::examples());
        $this->assertCount(substr_count($readme, "```php\n"), self::examples());
    }//end testEveryExampleHasItsOutput()

    /**
     * Tests that an example prints what the README says it prints.
     *
     * @param string $code     The PHP from the README.
     * @param string $expected The output from the README.
     *
     * @return void
     */
    #[DataProvider('examples')]
    public function testExampleProducesItsOutput(string $code, string $expected): void
    {
        ob_start();

        try {
            eval($code);
        } finally {
            $output = ob_get_clean();
        }

        $this->assertSame(rtrim($expected, "\n"), rtrim($output, "\n"));
    }//end testExampleProducesItsOutput()
}//end class
