<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html;

use DOMXPath;
use SupportPal\DomUtils\DOMDocument;
use SupportPal\DomUtils\Html\Filter\Filter;
use SupportPal\DomUtils\Html\Filter\RemoveDocType;
use SupportPal\DomUtils\Text\TextParser;
use SupportPal\DomUtils\Text\TextParserConfig;

use function count;
use function str_replace;
use function stripos;
use function strlen;
use function strspn;
use function substr;
use function trim;

class Html
{
    protected string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function toText(?TextParserConfig $config = null): string
    {
        return (new TextParser($this->html))->toText($config);
    }

    /**
     * Pre and post filter the HTML.
     *
     * Filters run in FILA (first in, last out) order. For example:
     * If there are three filters, named 1, 2 and 3, the order of execution is:
     *   1->preFilter, 2->preFilter, 3->preFilter,
     *   3->postFilter, 2->postFilter, 1->postFilter
     *
     * @param array<int, class-string> $filters
     * @return $this
     */
    public function filter(array $filters = []): self
    {
        foreach ($filters as $key => $class) {
            $filters[$key] = new $class;
        }

        for ($i = 0, $filter_size = count($filters); $i < $filter_size; $i++) {
            /** @var Filter $instance */
            $instance = $filters[$i];
            $this->html = $instance->preProcess($this->html);
        }

        for ($i = $filter_size - 1; $i >= 0; $i--) {
            /** @var Filter $instance */
            $instance = $filters[$i];
            $this->html = $instance->postProcess($this->html);
        }

        return $this;
    }

    /**
     * Get the HTML.
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Check if the document contains a HTML5 DOCTYPE.
     */
    public function isHtml5(): bool
    {
        return strspn($this->html, " \t\r\n") === stripos($this->html, '<!doctype html>');
    }

    /**
     * Check whether the given HTML contains any text.
     * NOTE: this function could return true, when the HTML only contains an image for example.
     */
    public function isEmpty(): bool
    {
        $dom = (new DOMDocument)->loadHTML($this->html);

        $xpath = new DOMXPath($dom->getInstance());

        // We don't care about the contents of these elements, we only care about human readable (visible) text.
        $alwaysRemove = ['head', 'style', 'script'];
        foreach ($alwaysRemove as $tagName) {
            foreach ($xpath->query('//' . $tagName) ?: [] as $node) {
                if ($node->parentNode === null) {
                    continue;
                }

                $node->parentNode->removeChild($node);
            }
        }

        // Remove empty HTML tags.
        foreach ($xpath->query('//*') ?: [] as $node) {
            if ($node->parentNode === null
                || (count($node->childNodes) && trim((string) $node->nodeValue, " \n\r\t\0\xC2\xA0") !== '')
            ) {
                continue;
            }

            $node->parentNode->removeChild($node);
        }

        $savedHtml = $dom->saveHTML();
        if ($savedHtml === false) {
            return false;
        }

        $savedHtml = str_replace(['<html>', '</html>', '<body>', '</body>'], ['', '', '', ''], $savedHtml);
        $savedHtml = (new Html($savedHtml))->filter([RemoveDocType::class])->getHtml();

        return strlen($savedHtml) === 0;
    }

    /**
     * Get the content of the <body> element.
     */
    public static function bodyContent(DOMDocument $dom): string
    {
        // Return the contents of the <body>, we don't want <!DOCTYPE><head><body>.
        $body = $dom->getElementsByTagName('body')->item(0);

        return trim(substr(
            (string) $dom->saveHTML($body),
            strlen('<body>'),
            -strlen('</body>')
        ));
    }
}
