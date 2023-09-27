<?php declare(strict_types=1);

namespace Test\Text;

use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use SupportPal\DomUtils\Html\Html;
use SupportPal\DomUtils\Text\TextParserConfig;
use Test\TestCase;

class TextParserTest extends TestCase
{
    #[DataProvider('toTextProvider')]
    public function testToText(int $i): void
    {
        $html = file_get_contents(__DIR__.'/fixtures/fixture'.$i.'.html') ?: throw new RuntimeException('Failed to open html file.');
        $text = file_get_contents(__DIR__.'/fixtures/fixture'.$i.'.txt') ?: throw new RuntimeException('Failed to open txt file.');
        $this->assertSame($text, (new Html($html))->toText());
    }

    /**
     * @return iterable<mixed>
     */
    public static function toTextProvider(): iterable
    {
        // Test cases from https://stackoverflow.com/a/30088920/2653593
        yield [1];
        yield [2];
        yield [3];

        //
        yield [4];
        yield [5];
        yield [6];
        yield [7];
        yield [8];
        yield [9];
        yield [10];
        yield [11];
        yield [12];
        yield [13];
    }

    #[DataProvider('marginProvider')]
    public function testToTextMargin(string $html, string $text): void
    {
        $config = (new TextParserConfig)->setBlockElementMargin(1);
        $this->assertSame($text, (new Html($html))->toText($config));
    }

    /**
     * @return iterable<mixed>
     */
    public static function marginProvider(): iterable
    {
        yield [
            '<html>
<body>
<p>foo</p>
<p>&nbsp;</p>
<p>bar</p>
<p>quz</p>
<p>&nbsp;</p>
<p>qux</p>
</body>
</html>',
            'foo
 
bar
quz
 
qux'
        ];
    }
}
