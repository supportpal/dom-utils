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

    /**
     * @return array<string, array{string, bool}>
     */
    public static function wellFormedDataProvider(): array
    {
        return [
            'minimal' => ['<html><head></head><body></body></html>', true],
            'with doctype, attributes and whitespace' => [
                "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<title>t</title></head>\n<body class=\"x\">\n<p>x</p></body>\n</html>\n",
                true,
            ],
            'uppercase' => ['<HTML><HEAD></HEAD><BODY>x</BODY></HTML>', true],
            'empty' => ['', false],
            'no root' => ['<p>x</p>', false],
            'text before html' => ['x<html><head></head><body></body></html>', false],
            'leading whitespace' => [' <html><head></head><body></body></html>', false],
            'text after html' => ['<html><head></head><body></body></html>x', false],
            'missing head' => ['<html><body></body></html>', false],
            'content between head and body' => ['<html><head></head>x<body></body></html>', false],
            'nested html tag' => ['<html><head></head><body><html></html></body></html>', false],
            'second body' => ['<html><head></head><body></body><body></body></html>', false],
            'doctype after html' => ['<html><!DOCTYPE html><head></head><body></body></html>', false],
            'header element is not head' => ['<html><header></header><body></body></html>', false],
        ];
    }

    #[DataProvider('wellFormedDataProvider')]
    public function testIsWellFormed(string $html, bool $expected): void
    {
        $this->assertSame($expected, Tidy::isWellFormed($html));

        // Parsing a well-formed document must be a no-op, and any parse result must itself be well-formed.
        $tidy = new Tidy;
        $tidy->loadHtml($html);
        $result = $tidy->saveHtml();

        if ($expected) {
            $this->assertSame($html, $result);
        }

        $this->assertTrue(Tidy::isWellFormed($result));
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
