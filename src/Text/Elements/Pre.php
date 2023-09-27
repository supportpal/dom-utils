<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use SupportPal\DomUtils\Text\Css\Breakline;
use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\Css\Margin;

class Pre extends Element
{
    public function startNode(): ?Content
    {
        if ($this->previous instanceof Margin || $this->previous instanceof Breakline) {
            return null;
        }

        return new Margin(1);
    }

    public function endNode(): ?Content
    {
        if ($this->previous instanceof Margin || $this->previous instanceof Breakline) {
            return null;
        }

        return new Margin(1);
    }
}
