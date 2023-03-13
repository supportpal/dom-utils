<?php declare(strict_types=1);

namespace Test\Html\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Filter\Trim;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class TrimTest extends TestCase
{
    #[DataProvider('trimProvider')]
    public function testHtml(string $string, string $expected): void
    {
        $result = (new Html($string))
            ->filter([Trim::class])
            ->getHtml();

        $this->assertSame($expected, $result);
    }

    /**
     * @return iterable<string[]>
     */
    public static function trimProvider(): iterable
    {
        yield ['<div>Foo</div>   ', '<div>Foo</div>'];
    }
}
