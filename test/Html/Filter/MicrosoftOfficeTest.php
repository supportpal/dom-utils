<?php declare(strict_types=1);

namespace Test\Html\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Filter\MicrosoftOffice;
use SupportPal\DomUtils\Html\Html;
use Test\TestCase;

class MicrosoftOfficeTest extends TestCase
{
    #[DataProvider('provider')]
    public function testHtml(string $string, string $expected): void
    {
        $result = (new Html($string))
            ->filter([MicrosoftOffice::class])
            ->getHtml();

        $this->assertSame($expected, $result);
    }

    /**
     * @return iterable<string[]>
     */
    public static function provider(): iterable
    {
        yield [
            '<o:p>Foo</o:p>',
            '<o:p>Foo</o:p>'
        ];

        yield [
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
<meta name="Generator" content="Microsoft Word 14 (filtered medium)">
</head>
<body><o:p>Foo</o:p></body>
</html>',
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
<meta name="Generator" content="Microsoft Word 14 (filtered medium)">
</head>
<body>Foo</body>
</html>'
        ];

        yield [
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head>
<![if !supportAnnotations]><style id="dynCom" type="text/css"><!-- --></style><![endif]>
</head>
</html>',
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head>
<!--[if !supportAnnotations]--><style id="dynCom" type="text/css"><!-- --></style><!--[endif]-->
</head>
</html>',
        ];

        yield [
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head>
<![if mso 9]><style id="dynCom" type="text/css"><!-- --></style><![endif]>
</head>
</html>',
            '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head>
<!--[if mso 9]--><style id="dynCom" type="text/css"><!-- --></style><!--[endif]-->
</head>
</html>',
        ];
    }
}
