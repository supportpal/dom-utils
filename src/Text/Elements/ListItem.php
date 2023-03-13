<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use SupportPal\DomUtils\Text\Css\Breakline;
use SupportPal\DomUtils\Text\Css\Content;

class ListItem extends Element
{
    public function startNode(): ?Content
    {
        return new Content('- ');
    }

    public function endNode(): ?Content
    {
        // No need for a new line on the last list item.
        if ($this->nextElementSibling($this->node) === null) {
            return null;
        }

        return new Breakline;
    }
}
