<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html;

use function explode;
use function ltrim;
use function preg_match;
use function preg_replace;
use function sprintf;
use function stripos;
use function stristr;
use function strlen;
use function strtolower;

/**
 * @phpstan-type TreeNode 'doctype'|'html'|'head'|'body'
 * @phpstan-type TreeNodeValue array{'start': string, 'end': string, 'content': array<int, string>}
 */
class Tidy
{
    /**
     * Structure of a basic HTML document.
     *
     * @var array{'doctype': string, 'html': TreeNodeValue, 'head': TreeNodeValue, 'body': TreeNodeValue}
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
        $i = 0;
        while ($i < strlen($html)) {
            if ($html[$i] === '<') {
                // Found a tag, get chars until the end of the tag.
                $tag = '';
                while ($i < strlen($html) && $html[$i] !== '>') {
                    $tag .= $html[$i++];
                }

                if ($i < strlen($html) && $html[$i] === '>') {
                    $tag .= $html[$i++];

                    // Copy any whitespace following the tag.
                    // Anything added here needs to be added to the rtrim in the nodeName function.
                    while ($i < strlen($html) && preg_match('/\s/', $html[$i])) {
                        $tag .= $html[$i++];
                    }
                } else {
                    // Missing closing tag?
                    $tag .= '>';
                }

                $this->addToTree($tag);
            } else {
                $this->addToTree($html[$i++]);
            }
        }
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
     * Add a node to the the tree.
     *
     * @phpstan-param TreeNode $key
     */
    protected function addTo(string $key, string $node, bool $setPrevious = true): bool
    {
        $previousKey = $key;

        if (stripos($node, sprintf('<%s', $key)) !== false) {
            $this->tree[$key]['start'] = $node; // @phpstan-ignore-line
        } elseif (stristr($node, sprintf('/%s>', $key))) {
            $this->tree[$key]['end'] = $node;   // @phpstan-ignore-line
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
