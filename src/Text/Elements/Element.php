<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMNode;
use DOMText;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\TextParserConfig;

use function preg_replace;
use function rtrim;
use function str_replace;
use function strlen;
use function trim;

abstract class Element
{
    use DOMHelpers;

    protected TextParserConfig $config;

    /** @var DOMNode */
    protected $node;

    protected ?Content $previous;

    public function __construct(TextParserConfig $config, DOMNode $node, ?Content $previous)
    {
        $this->config = $config;
        $this->node = $node;
        $this->previous = $previous;
    }

    abstract public function startNode(): ?Content;

    abstract public function endNode(): ?Content;

    public function nextSibling(bool $ignoreWhitespace = true): ?DOMNode
    {
        $node = $this->node->nextSibling;
        while ($node !== null) {
            if ($ignoreWhitespace && $node instanceof DOMText && $this->isWhitespace($node->wholeText)) {
                $node = $node->nextSibling;
                continue;
            }

            return $node;
        }

        return null;
    }

    protected function isWhitespace(string $text): bool
    {
        return strlen(trim($this->processWhitespace($text), "\n\r\t ")) === 0;
    }

    protected function removeZwnjCodes(string $text): string
    {
        return str_replace($this->zwnjCodes(), '', $text);
    }

    protected function processWhitespace(string $text): string
    {
        $text = rtrim($text);
        $text = $this->removeZwnjCodes($text);
        $text = (string) preg_replace("/[\\t\\n\\f\\r ]+/im", ' ', $text);

        return trim($text);
    }

    /**
     * @return string[]
     */
    protected function nbspCodes(): array
    {
        return [
            "\xc2\xa0",
            "\u00a0",
        ];
    }

    /**
     * @return string[]
     */
    protected function zwnjCodes(): array
    {
        return [
            "\xe2\x80\x8c",
            "\u200c",
        ];
    }
}
