<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMText;
use SupportPal\DomUtils\Text\Css\Content;

use function in_array;
use function str_replace;
use function trim;

class Text extends Element
{
    /** @var DOMText */
    protected $node;

    public function startNode(): ?Content
    {
        return null;
    }

    public function endNode(): ?Content
    {
        $text = $this->node->wholeText;
        if ($this->isWhitespace($text)) {
            return null;
        }

        if (! in_array($this->node->parentNode?->nodeName, ['pre'])) {
            $text = $this->processWhitespace($text);
        }

        $text = $this->removeZwnjCodes($text);
        $text = str_replace($this->nbspCodes(), ' ', trim($text));

        if (empty($text)) {
            return null;
        }

        return new Content($text);
    }
}
