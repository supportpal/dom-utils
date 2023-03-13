<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMNode;
use DOMText;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\TextParserConfig;

use function array_key_exists;
use function in_array;
use function strtolower;

class ContentModel
{
    /**
     * Elements with default margin-top / margin-bottom CSS.
     *
     * @var string[]
     */
    private array $margin = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'menu', 'hr', 'ol', 'ul', 'blockquote', 'dl', 'figure',
    ];

    /** @var string[] */
    private array $block = [
        'address',
        'article',
        'aside',
        'blockquote',
        'details',
        'dialog',
        'dd',
        'div',
        'dl',
        'dt',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'header',
        'hgroup',
        'hr',
        'li',
        'main',
        'nav',
        'ol',
        'p',
        'pre',
        'section',
        'table',
        'ul',
        '#text',
    ];

    /** @var string[] */
    private array $inline = [
        'a',
        'abbr',
        'acronym',
        'audio',
        'b',
        'bdi',
        'bdo',
        'big',
        'br',
        'button',
        'canvas',
        'cite',
        'code',
        'data',
        'datalist',
        'del',
        'dfn',
        'em',
        'embed',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'map',
        'mark',
        'meter',
        'noscript',
        'object',
        'output',
        'picture',
        'progress',
        'q',
        'ruby',
        's',
        'samp',
        'script',
        'select',
        'slot',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'svg',
        'template',
        'textarea',
        'time',
        'u',
        'tt',
        'var',
        'video',
        'wbr',
    ];

    /** @var array<string, class-string> */
    private array $mapping = [
        'h1'  => MarginBlock::class,
        'h2'  => MarginBlock::class,
        'h3'  => MarginBlock::class,
        'h4'  => MarginBlock::class,
        'h5'  => MarginBlock::class,
        'h6'  => MarginBlock::class,
        'p'   => MarginBlock::class,
        'ul'  => MarginBlock::class,
        'ol'  => MarginBlock::class,
        'li'  => ListItem::class,
        'br'  => Br::class,
        'hr'  => Hr::class,
        'div' => Div::class,
        'tr'  => TableRow::class,
        'td'  => TableCell::class,
        'th'  => TableCell::class,
        'img' => Image::class,
        'a'   => Anchor::class,
    ];

    private DOMNode $node;

    public function __construct(DOMNode $node)
    {
        $this->node = $node;
    }

    public function start(TextParserConfig $config, ?Content $previous): ?Content
    {
        $name = strtolower($this->node->nodeName);
        if (! array_key_exists($name, $this->mapping)) {
            return null;
        }

        /** @var Element $element */
        $element = new $this->mapping[$name]($config, $this->node, $previous);

        return $element->startNode();
    }

    public function end(TextParserConfig $config, ?Content $previous): ?Content
    {
        if ($this->node instanceof DOMText) {
            return (new Text($config, $this->node, $previous))->endNode();
        }

        $name = strtolower($this->node->nodeName);
        if (! array_key_exists($name, $this->mapping)) {
            return null;
        }

        /** @var Element $element */
        $element = new $this->mapping[$name]($config, $this->node, $previous);

        return $element->endNode();
    }

    public function hasMargin(): bool
    {
        return in_array(strtolower($this->node->nodeName), $this->margin, true);
    }

    public function isBlockElement(): bool
    {
        return in_array(strtolower($this->node->nodeName), $this->block, true);
    }

    public function isInlineElement(): bool
    {
        return in_array(strtolower($this->node->nodeName), $this->inline, true);
    }
}
