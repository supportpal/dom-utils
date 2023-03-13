<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMElement;
use SupportPal\DomUtils\Text\Css\Content;

use function preg_match;
use function sprintf;

class Anchor extends Element
{
    public function startNode(): ?Content
    {
        return null;
    }

    public function endNode(): ?Content
    {
        if (! $this->node instanceof DOMElement
            || ! $this->node->hasAttribute('href')
        ) {
            return null;
        }

        // Ensure there's whitespace between the anchor text and the URL.
        $format = '[%s]';
        if (! preg_match('/\s$/', (string) $this->previous)) {
            $format = ' ' . $format;
        }

        return new Content(sprintf($format, $this->node->getAttribute('href')));
    }
}
