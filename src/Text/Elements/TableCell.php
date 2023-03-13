<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use SupportPal\DomUtils\Text\Css\Content;

use function str_ends_with;

class TableCell extends Element
{
    public function startNode(): ?Content
    {
        if (str_ends_with((string) $this->previous, "\n")) {
            return null;
        }

        return new Content("\t");
    }

    public function endNode(): ?Content
    {
        return null;
    }
}
