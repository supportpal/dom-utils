<?php declare(strict_types=1);

namespace Test\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Tidy;
use Test\TestCase;

use function str_repeat;

class TidyTest extends TestCase
{
    /**
     * @return string[][]
     */
    public static function htmlDataProvider(): array
    {
        return [
            'empty' => ['', '<html><head></head><body></body></html>'],
            'whitespace after tags is kept with the tag' => [
                "<html>\n\n<head>\t<title>t</title>\n</head>\r\n<body>  <p>x</p>\n</body>\n</html>\n",
                "<html>\n\n<head>\t<title>t</title>\n</head>\r\n<body>  <p>x</p>\n</body>\n</html>\n",
            ],
            'text containing a closing bracket' => [
                '<p>a > b</p>',
                '<html><head></head><body><p>a > b</p></body></html>',
            ],
            'text resembling a closing root tag is kept as text' => [
                'see /body> here',
                '<html><head></head><body>see /body> here</body></html>',
            ],
            'unterminated tag at the end' => [
                '<p>x</p><b  ',
                '<html><head></head><body><p>x</p><b  ></body></html>',
            ],
            'lone opening bracket' => [
                'a <',
                '<html><head></head><body>a <></body></html>',
            ],
            'closing bracket inside an attribute ends the tag' => [
                '<div title="a>b">x</div>',
                '<html><head></head><body><div title="a>b">x</div></body></html>',
            ],
            'comment' => [
                '<!-- c --><p>x</p>',
                '<html><head></head><body><!-- c --><p>x</p></body></html>',
            ],
            'only the first doctype is kept' => [
                '<!DOCTYPE a><!DOCTYPE b><html></html>',
                '<!DOCTYPE a><html><head></head><body></body></html>',
            ],
            'content before the html tag goes into the body' => [
                "junk\n<html><head></head><body>b</body></html>",
                "<html><head></head><body>junk\nb</body></html>",
            ],
        ];
    }

    #[DataProvider('htmlDataProvider')]
    public function testLoadAndSave(string $html, string $expected): void
    {
        $tidy = new Tidy;
        $tidy->loadHtml($html);

        $this->assertSame($expected, $tidy->saveHtml());
    }

    public function testLargeDocument(): void
    {
        $body = str_repeat('<div class="x"><p>' . str_repeat('word ', 200) . '</p></div>' . "\n", 2000);
        $html = '<html><body>' . $body . '</body></html>';

        $tidy = new Tidy;
        $tidy->loadHtml($html);

        $this->assertSame('<html><head></head><body>' . $body . '</body></html>', $tidy->saveHtml());
    }
}
