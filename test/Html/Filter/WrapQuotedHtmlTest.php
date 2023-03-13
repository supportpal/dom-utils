<?php declare(strict_types=1);

namespace Test\Html\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\DOMDocument;
use SupportPal\DomUtils\Html\Filter\MakeWellFormed;
use SupportPal\DomUtils\Html\Filter\WrapQuotedHtml;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

use function str_replace;

class WrapQuotedHtmlTest extends TestCase
{
    /** @var non-empty-string */
    private string $container = '<div class="expandable"></div><div class="supportpal_quote">';

    #[DataProvider('htmlProvider')]
    public function testHtml(string $string, ?string $expected = null): void
    {
        $result = (new Html($string))
            ->filter([MakeWellFormed::class, WrapQuotedHtml::class])
            ->getHtml();

        $dom = (new DOMDocument)->loadHTML($result);
        $result = str_replace("\n", '', Html::bodyContent($dom));

        if ($expected === null) {
            $this->assertStringStartsWith($this->container, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /**
     * @return iterable<string[]>
     */
    public static function htmlProvider(): iterable
    {
        // mozilla
        yield ['<div class="moz-cite-prefix">Foo</div>'];

        // mozilla plaintext signature
        yield ['<pre class="moz-signature" cols="72">Foo</pre>'];

        // generic
        yield ['<blockquote>Foo</blockquote>'];

        yield ['<blockquote></blockquote>', '<blockquote></blockquote>'];

        // outlook
        yield ['<hr id="stopSpelling">Foo'];

        yield ['<div id="divRplyFwdMsg">Foo</div>'];

        yield ['<div style="border:none;border-top:solid #E1E1E1 1.0pt;padding:3.0pt 0in 0in 0in">Foo</div>'];

        yield ['<div style="border:none;border-top:solid #B5C4DF 1.0pt;padding:3.0pt 0in 0in 0in">Foo</div>'];

        // web.de
        yield ['<div name="quote">Foo</div>'];

        // gmail
        yield ['<div class="gmail_extra">Foo</div>'];

        yield ['<div class="gmail_quote">Foo</div>'];

        // airmail
        yield ['<p class="airmail_on">Foo</p>'];

        yield ['<p class="gmail_quote">Foo</p>'];

        yield ['<div class="airmail_ext_on">Foo</div>'];

        // zimbra
        yield ['<hr id="zwchr"><div>Foo</div>'];

        // applemail
        yield ['<div id="AppleMailSignature">Foo</div>'];

        // yahoo
        yield ['<div class="yahoo_quoted">Foo</div>'];
    }
}
