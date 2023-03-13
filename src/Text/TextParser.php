<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text;

use DOMNode;
use SupportPal\DomUtils\DOMDocument;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\Elements\ContentModel;

use function end;
use function implode;
use function in_array;
use function strtolower;
use function trim;

use const XML_COMMENT_NODE;
use const XML_DOCUMENT_TYPE_NODE;
use const XML_PI_NODE;

class TextParser
{
    private string $html;

    /** @var int[] */
    private array $skipNodes = [XML_COMMENT_NODE, XML_DOCUMENT_TYPE_NODE, XML_PI_NODE];

    /** @var string[] */
    private array $skipElements = ['head', 'title', 'meta', 'script', 'style'];

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function toText(?TextParserConfig $config = null): string
    {
        $config = $config ?? new TextParserConfig;
        $dom = (new DOMDocument)->loadHTML($this->html);

        return trim(implode('', $this->convertNodeToText($dom->getInstance(), $config)));
    }

    /**
     * @return int[]
     */
    public function getSkipNodes(): array
    {
        return $this->skipNodes;
    }

    /**
     * @param int[] $nodeTypes
     */
    public function setSkipNodes(array $nodeTypes): self
    {
        $this->skipNodes = $nodeTypes;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSkipElements(): array
    {
        return $this->skipElements;
    }

    /**
     * @param string[] $elements
     */
    public function setSkipElements(array $elements): self
    {
        $this->skipElements = $elements;

        return $this;
    }

    private function skipNode(DOMNode $node): bool
    {
        return in_array($node->nodeType, $this->getSkipNodes(), true)
            || in_array(strtolower($node->nodeName), $this->getSkipElements(), true);
    }

    /**
     * @param Content[] $parts
     * @return Content[]
     */
    private function convertNodeToText(DOMNode $node, TextParserConfig $config, array $parts = []): array
    {
        if ($this->skipNode($node)) {
            return [];
        }

        $contentModel = new ContentModel($node);
        $this->pushIfNotNull($parts, $contentModel->start($config, $this->last($parts)));

        $i = 0;
        while (($childNode = $node->childNodes->item($i++)) !== null) {
            $parts = $parts + $this->convertNodeToText($childNode, $config, $parts);
        }

        $this->pushIfNotNull($parts, $contentModel->end($config, $this->last($parts)));

        return $parts;
    }

    /**
     * @param Content[] $parts
     */
    private function pushIfNotNull(array &$parts, ?Content $part): void
    {
        if ($part === null) {
            return;
        }

        $parts[] = $part;
    }

    /**
     * @param array<int, Content> $array
     */
    private function last(array $array): ?Content
    {
        return empty($array) ? null : end($array);
    }
}
