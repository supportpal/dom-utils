<?php declare(strict_types=1);

namespace Test\Html\Filter;

use SupportPal\DomUtils\DOMDocument;
use SupportPal\DomUtils\Html\Filter\InlineStylesheets;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class InlineStylesheetsTest extends TestCase
{
    /** @dataProvider htmlProvider */
    public function testHtml(string $html, string $expected): void
    {
        $result = (new Html($html))
            ->filter([InlineStylesheets::class])
            ->getHtml();

        $dom = (new DOMDocument)->loadHTML($result);
        $result = Html::bodyContent($dom);

        $this->assertSame($expected, $result);
    }

    /**
     * @return iterable<mixed>
     */
    public static function htmlProvider(): iterable
    {
        yield ['<style>p{color: red}</style><p>Foo</p>', '<p style="color: red;">Foo</p>'];

        yield ['<style>p{color: red !important}</style><p>Foo</p>', '<p style="color: red !important;">Foo</p>'];
    }
}
