<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use SupportPal\DomUtils\Text\Css\Content;
use SupportPal\DomUtils\Text\Css\Margin;

class Hr extends Element
{
    private string $content = '---------------------------------------------------------------';

    public function startNode(): ?Content
    {
        if ($this->previous instanceof Margin) {
            return new Content($this->content);
        }

        return (new Margin(1))->appendContent($this->content);
    }

    public function endNode(): ?Content
    {
        return new Margin(1);
    }
}
