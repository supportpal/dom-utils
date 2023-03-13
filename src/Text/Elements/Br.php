<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMElement;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\Css\Margin;

use const XML_TEXT_NODE;

class Br extends Element
{
    /** @var DOMElement */
    protected $node;

    public function startNode(): ?Content
    {
        return null;
    }

    public function endNode(): ?Content
    {
        // Break lines directly after text, in the context of a parent block node are
        // redundant from a margin perspective.
        if ($this->node->previousSibling?->nodeType === XML_TEXT_NODE
            && $this->nextSibling() === null
            && isset($this->node->parentNode)
            && (new ContentModel($this->node->parentNode))->isBlockElement()
        ) {
            return null;
        }

        return new Margin(1);
    }
}
