<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html;

use function array_keys;
use function array_slice;
use function count;
use function explode;
use function ltrim;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function stripos;
use function stristr;
use function strlen;
use function strpos;
use function strspn;
use function strtolower;
use function substr;

use const PREG_OFFSET_CAPTURE;

/**
 * @phpstan-type Tree array{'doctype': string, 'html': TreeNodeValue, 'head': TreeNodeValue, 'body': TreeNodeValue}
 * @phpstan-type TreeNode 'html'|'head'|'body'
 * @phpstan-type TreeNodeValue array{'start': string, 'end': string, 'content': array<int, string>}
 */
class Tidy
{
    /**
     * Structure of a basic HTML document.
     *
     * @var Tree
     */
    protected array $tree = [
        'doctype' => '',
        'html'    => [
            'start'   => '<html>',
            'end'     => '</html>', // can't have attributes on closing html tags
            'content' => [],
        ],
        'head'    => [
            'start'   => '<head>', // can't have attributes on head tag
            'end'     => '</head>', // can't have attributes on closing head tag
            'content' => []
        ],
        'body'    => [
            'start'   => '<body>',
            'end'     => '</body>', // can't have attributes on closing body tag
            'content' => []
        ],
    ];

    /**
     * What root element did we last add to.
     *
     * @phpstan-var TreeNode|null $previousKey
     */
    protected ?string $previousKey = null;

    /**
     * Parse a HTML document.
     */
    public function loadHtml(string $html): void
    {
        $length = strlen($html);
        $i = 0;

        while ($i < $length) {
            $start = strpos($html, '<', $i);
            if ($start === false) {
                $this->addToTree(substr($html, $i));
                break;
            }

            if ($start > $i) {
                $this->addToTree(substr($html, $i, $start - $i));
            }

            $end = strpos($html, '>', $start);
            if ($end === false) {
                // Missing closing tag?
                $this->addToTree(substr($html, $start) . '>');
                break;
            }

            // Copy any whitespace following the tag.
            // Anything added here needs to be added to the rtrim in the nodeName function.
            $end += 1 + strspn($html, " \t\n\r\x0B\f", $end + 1);

            $this->addToTree(substr($html, $start, $end - $start));
            $i = $end;
        }
    }

    /**
     * Whether the document is already in the shape produced by saveHtml(): an optional doctype, then html, head and
     * body elements in order with nothing but whitespace between them. Parsing such a document is a no-op.
     */
    public static function isWellFormed(string $html): bool
    {
        if (preg_match('/\A(?:<!doctype[^>]*>\s*)?<html(?:\s[^>]*)?>\s*<head(?:\s[^>]*)?>/i', $html) !== 1) {
            return false;
        }

        if (preg_match('/<\/body>\s*<\/html>\s*\z/i', $html) !== 1) {
            return false;
        }

        // Each root element must open and close exactly once, in order, with only whitespace between </head> and <body>.
        $tags = ['<!doctype', '<html', '<head', '</head', '<body', '</body', '</html'];
        $count = preg_match_all('/<(?:!doctype|\/?html|\/?head|\/?body)(?=[\s>])/i', $html, $matches, PREG_OFFSET_CAPTURE);
        if ($count === false || $count > 7) {
            return false;
        }

        $found = [];
        foreach ($matches[0] as [$tag, $offset]) {
            $found[strtolower($tag)] = $offset;
        }

        $expected = $found === [] ? [] : (isset($found['<!doctype']) ? $tags : array_slice($tags, 1));
        if (count($found) !== count($expected) || array_keys($found) !== $expected) {
            return false;
        }

        $headEnd = strpos($html, '>', $found['</head']);

        return $headEnd !== false && strspn($html, " \t\n\r\x0B\f", $headEnd + 1) === $found['<body'] - $headEnd - 1;
    }

    /**
     * Format the document in a structured way.
     */
    public function saveHtml(): string
    {
        // Initialise buffer.
        $buffer = '';

        // Add <!DOCTYPE> - this is optional.
        $buffer .= $this->tree['doctype'];

        // Add <html>
        $buffer .= $this->tree['html']['start'];

        // Add head
        $buffer .= $this->tree['head']['start'];
        foreach ($this->tree['head']['content'] as $node) {
            $buffer .= $node;
        }

        $buffer .= $this->tree['head']['end'];

        // Add body
        $buffer .= $this->tree['body']['start'];
        foreach ($this->tree['body']['content'] as $node) {
            $buffer .= $node;
        }

        $buffer .= $this->tree['body']['end'];

        // Close </html> tag
        return $buffer . $this->tree['html']['end'];
    }

    /**
     * Add a node into the tree for the correct parent.
     */
    protected function addToTree(string $node): bool
    {
        if ($node[0] === '<') {
            switch (strtolower($this->nodeName($node))) {
                case '!doctype':
                    if (empty($this->tree['doctype'])) {
                        $this->tree['doctype'] = $node;
                    }

                    // Don't overwrite if we've already got a doctype definition.
                    return true;

                case 'html':
                    return $this->addTo('html', $node, false);

                case 'head':
                    return $this->addTo('head', $node);

                default:
                    return $this->addTo($this->previousKey ?? 'body', $node);
            }
        }

        // text node
        return $this->addTo($this->previousKey ?? 'body', $node);
    }

    /**
     * Add a node to the tree.
     *
     * @phpstan-param TreeNode $key
     */
    protected function addTo(string $key, string $node, bool $setPrevious = true): bool
    {
        $previousKey = $key;

        if ($node[0] !== '<') {
            $this->tree[$key]['content'][] = $node;
        } elseif (stripos($node, sprintf('<%s', $key)) !== false) {
            $this->tree[$key]['start'] = $node;
        } elseif (stristr($node, sprintf('/%s>', $key))) {
            $this->tree[$key]['end'] = $node;
            $previousKey = null;
        } else {
            $this->tree[$key]['content'][] = $node;
        }

        if ($setPrevious) {
            $this->previousKey = $previousKey;
        }

        return true;
    }

    /**
     * Get the name of a node without </>
     */
    protected function nodeName(string $node): string
    {
        $name = (string) preg_replace('/>\s*/', '', ltrim($node, '</'));

        return explode(' ', $name)[0];
    }
}
