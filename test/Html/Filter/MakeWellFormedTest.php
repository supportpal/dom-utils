<?php declare(strict_types=1);

namespace Test\Html\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Filter\MakeWellFormed;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class MakeWellFormedTest extends TestCase
{
    /**
     * The aim of the BasicHtmlParser is to add missing root elements to HTML. It needs to be able
     * to handle badly formatted HTML without throwing an error so this is what we're testing here.
     *
     * @return string[][]
     */
    public static function invalidHtmlDataProvider(): array
    {
        return [
            [
                '<head>',
                '<html><head></head><body></body></html>'
            ],
            [
                '</head>',
                '<html><head></head><body></body></html>'
            ],
            [
                '<head><meta charset="utf8" /></head>',
                '<html><head><meta charset="utf8" /></head><body></body></html>'
            ],
            [
                '<meta charset="utf8" /></head>',
                '<html><head></head><body><meta charset="utf8" /></body></html>'
            ],
            [
                '<meta charset="utf8" />',
                '<html><head></head><body><meta charset="utf8" /></body></html>'
            ],
            [
                '<body>',
                '<html><head></head><body></body></html>'
            ],
            [
                '<body>Hi</body>',
                '<html><head></head><body>Hi</body></html>'
            ],
            [
                'Hi</body>',
                '<html><head></head><body>Hi</body></html>'
            ],
            [
                'Hi',
                '<html><head></head><body>Hi</body></html>'
            ],
            [
                '<b',
                '<html><head></head><body><b></body></html>'
            ],
            [
                '<html>',
                '<html><head></head><body></body></html>'
            ],
            [
                '<html>Hi</html>',
                '<html><head></head><body>Hi</body></html>'
            ],
            [
                'Hi</html>',
                '<html><head></head><body>Hi</body></html>'
            ],
            [
                "  <html>\n  Hi</html>   <body></body>",
                "<html>\n  <head></head><body>  Hi</body></html>   "
            ],
        ];
    }

    #[DataProvider('invalidHtmlDataProvider')]
    public function testRenderRepairsBrokenHtml(string $string, string $expected): void
    {
        $result = (new Html($string))
            ->filter([MakeWellFormed::class])
            ->getHtml();

        $this->assertSame($expected, $result);
    }
}
