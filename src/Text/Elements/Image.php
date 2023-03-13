<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMElement;
use SupportPal\DomUtils\Text\Css\Content;

use function sprintf;

class Image extends Element
{
    public function startNode(): ?Content
    {
        return null;
    }

    public function endNode(): ?Content
    {
        if (! $this->node instanceof DOMElement
            || ! $this->node->hasAttribute('src')
        ) {
            return null;
        }

        return new Content(sprintf('![%s]', $this->node->getAttribute('src')));
    }
}
