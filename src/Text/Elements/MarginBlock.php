<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMElement;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\Css\Margin;

use function max;

class MarginBlock extends Element
{
    /** @var DOMElement */
    protected $node;

    public function startNode(): ?Content
    {
        if ($this->previous instanceof Margin) {
            $margin = max($this->config->getBlockElementMargin() - $this->previous->getMargin(), 0);
            if ($margin === 0) {
                return null;
            }

            return new Margin($margin);
        }

        return new Margin($this->config->getBlockElementMargin());
    }

    public function endNode(): ?Content
    {
        if ($this->previous instanceof Margin) {
            $margin = max($this->config->getBlockElementMargin() - $this->previous->getMargin(), 0);
            if ($margin === 0) {
                return null;
            }

            return new Margin($margin);
        }

        return new Margin($this->config->getBlockElementMargin());
    }
}
