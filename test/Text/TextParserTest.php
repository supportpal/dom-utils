<?php declare(strict_types=1);

namespace Test\Text;

use PHPUnit\Framework\Attributes\DataProvider;
use SupportPal\DomUtils\Html\Html;
use SupportPal\DomUtils\Text\TextParserConfig;
use Test\TestCase;

class TextParserTest extends TestCase
{
    #[DataProvider('toTextProvider')]
    public function testToText(string $html, string $text): void
    {
        $this->assertSame($text, (new Html($html))->toText());
    }

    /**
     * @return iterable<mixed>
     */
    public static function toTextProvider(): iterable
    {
        // Test cases from https://stackoverflow.com/a/30088920/2653593
        yield [
            '<div><div><div>line1</div></div></div><div>line2</div>',
            'line1
line2',
        ];

        yield ['<span>1<style> text </style><i>2</i></span>3', '123'];

        yield [
            '<div>  1 </div>  2 <div> 3  </div>',
            '1
2
3',
        ];

        //

        yield [
            '<a href="#foo">foo</a><br><a href="#foo"><p>foo</p></a>',
            'foo [#foo]

foo

[#foo]'
        ];

        yield [
            '<img><img src="one.png">',
            '![one.png]',
        ];

        yield [
            '<html>
<body>
<div>
Hello
<br>
</div>
<div>
How are you?
<br>
</div>

<p>
How are you?
<br>
</p>

<p>
How are you?
<br>
</p>

<div>
Just two divs
</div>
<div>
Hanging out
</div>

This is not the end!
<div>
How are you again?
<br>
</div>
This is the end!
<br>
Just kidding
<h1>Header 1</h1>
Some text
<hr>
Some more text
<p>Paragraph tag!</p>
<h2>Header 2</h2>
<hr>
<h3>Header 3</h3>
Some text
<h4>Header 4</h4>
<p>Paragraph tag!</p>
Final line
</body>
</html>',
            'Hello
How are you?

How are you?

How are you?

Just two divs
Hanging out
This is not the end!
How are you again?
This is the end!
Just kidding

Header 1

Some text
---------------------------------------------------------------
Some more text

Paragraph tag!

Header 2

---------------------------------------------------------------

Header 3

Some text

Header 4

Paragraph tag!

Final line'
        ];

        yield [
            '1<br />2<br />3<br />4<br />5 &lt; 6',
            '1
2
3
4
5 < 6'
        ];

        yield ['<p>foo&zwnj;bar</p>', 'foobar'];

        yield [
            '<html>
<body>
<div>
Just two divs
</div>
<div>
Hanging out
</div>
<div><div><div>Nested divs and line breaks</div></div><br></div>
<div><div>Nested divs and line breaks</div>More text<br></div>
<div><br></div>
<div>Just text</div>
<div>Just text<br></div>
<div>Just text<br><br></div>
This is the end!
</body>
</html>
',
            'Just two divs
Hanging out
Nested divs and line breaks

Nested divs and line breaks
More text

Just text
Just text
Just text

This is the end!'
        ];

        yield [
            '<html>
<title>Ignored Title</title>
<body>
  <h1>Hello, World!</h1>

  <p>This is some e-mail content.
  Even though it has whitespace and newlines, the e-mail converter
  will handle it correctly.</p>

  <p>Even mismatched tags.</p>

  <div>A div</div>
  <div>Another div</div>
  <div>A div<div>within a div</div></div>

  <p>Another line<br />Yet another line</p>

  <a href="http://foo.com">A link</a>

</body>
</html>',
            'Hello, World!

This is some e-mail content. Even though it has whitespace and newlines, the e-mail converter will handle it correctly.

Even mismatched tags.

A div
Another div
A div
within a div

Another line
Yet another line

A link [http://foo.com]',
        ];

        yield [
            '<html>
<title>Ignored Title</title>
<body>
  <h1>Hello, World!</h1>
  <table>
    <thead>
      <tr>
        <th>Col A</th>
        <th>Col B</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          Data A1
        </td>
        <td>
          Data B1
        </td>
      </tr>
      <tr>
          <td>
            Data A2
          </td>
          <td>
            Data B2
          </td>
      </tr>
      <tr>
        <td>
          Data A3
        </td>
        <td>
          Data B4
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
          <td>
            Total A
          </td>
          <td>
            Total B
          </td>
       </tr>

    </tfoot>

  </table>

</body>
</html>',
            'Hello, World!

Col A	Col B
Data A1	Data B1
Data A2	Data B2
Data A3	Data B4
Total A	Total B',
        ];

        yield [
            '<h1>List tests</h1>

<p>
Add some lists.
</p>

<ol>
	<li>one</li>
	<li>two
	<li>three
</ol>

<h2>An unordered list</h2>

<ul>
	<li>one
	<li>two</li>
	<li>three</li>	
</ul>
<ul>
	<li>one
	<li>two</li>
	<li>three</li>	
</ul>',
            'List tests

Add some lists.

- one
- two
- three

An unordered list

- one
- two
- three

- one
- two
- three',
        ];
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
