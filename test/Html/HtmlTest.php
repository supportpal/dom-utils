<?php declare(strict_types=1);

namespace Test\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class HtmlTest extends TestCase
{
    #[DataProvider('isEmptyProvider')]
    public function testIsEmpty(string $html, bool $expected): void
    {
        $this->assertSame($expected, (new Html($html))->isEmpty());
    }

    /**
     * @return iterable<mixed>
     */
    public static function isEmptyProvider(): iterable
    {
        yield ['<blockquote></blockquote>', true];
        yield ['<blockquote>Foo</blockquote>', false];
        //yield ['<blockquote><img></blockquote>', false];
    }
}
