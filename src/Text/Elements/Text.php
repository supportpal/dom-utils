<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMText;
use SupportPal\DomUtils\Text\Css\Content;

use function str_replace;

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

        $text = trim(str_replace([$this->nbspCodes(), $this->zwnjCodes()], ' ', $text));

        if (empty($text)) {
            return null;
        }

        return new Content($text);
    }
}
