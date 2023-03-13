<?php declare(strict_types=1);

namespace Test\Html\Filter;

use SupportPal\DomUtils\Html\Filter\AddContentTypeMetaTag;
use SupportPal\DomUtils\Html\Filter\MakeWellFormed;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class AddContentTypeMetaTagTest extends TestCase
{
    /** @dataProvider htmlProvider */
    public function testAddMetaInHtml5(string $input, string $expected): void
    {
        $html = (new Html($input))->filter([MakeWellFormed::class, AddContentTypeMetaTag::class]);
        $this->assertSame($expected, $html->getHtml());
    }

    /**
     * @return iterable<string[]>
     */
    public static function htmlProvider(): iterable
    {
        yield [
            '<!DOCTYPE html><html><head><meta charset="iso-8859-1_X"></head><body>Test1<br>Test2',
            '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>Test1<br>Test2</body></html>',
        ];

        yield [
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body>Test1<br>Test2',
            '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>Test1<br>Test2</body></html>',
        ];

        yield [
            '<!DOCTYPE html>Test1<br>Test2',
            '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>Test1<br>Test2</body></html>',
        ];

        yield [
            '<!DOCTYPE html><html>Test1<br>Test2',
            '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>Test1<br>Test2</body></html>',
        ];

        yield [
            '<!DOCTYPE html><html><head></head>Test1<br>Test2',
            '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>Test1<br>Test2</body></html>',
        ];
    }
}
