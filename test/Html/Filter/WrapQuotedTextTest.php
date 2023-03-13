<?php declare(strict_types=1);

namespace Test\Html\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Filter\WrapQuotedText;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class WrapQuotedTextTest extends TestCase
{
    /** @var non-empty-string */
    private string $container = '<div class="expandable"></div><div class="supportpal_quote">';

    #[DataProvider('plainTextProvider')]
    public function testPlainText(string $string, ?string $expected = null): void
    {
        $result = (new Html($string))
            ->filter([WrapQuotedText::class])
            ->getHtml();

        if ($expected === null) {
            $this->assertStringStartsWith($this->container, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /**
     * @return iterable<string[]>
     */
    public static function plainTextProvider(): iterable
    {
        yield ['On 2 June 2016 at 17:40, Kieran Brahney &lt;kieran.brahney@gmail.com&gt; wrote:'];

        yield ['On 04/28/2016 20:29, LU Hiking Club wrote:'];

        yield ['On 28. 06. 2016 21:12, Domen Cesnik / Zabec.net wrote:'];

        yield ["Date: Sat, 23 Jan 2016 20:37:17 +0000\n"];

        yield ["From: LU Hiking Club [mailto:hiking@lancaster.ac.uk]\n"];

        yield [
            '________________________________
De : Hiking Club
'
        ];

        yield ["De: SupportPal Services <services@supportpal.com>\n"];
    }
}
