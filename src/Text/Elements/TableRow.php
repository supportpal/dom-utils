<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use SupportPal\DomUtils\Text\Css\Breakline;
use SupportPal\DomUtils\Text\Css\Content;

class TableRow extends Element
{
    public function startNode(): ?Content
    {
        return null;
    }

    public function endNode(): ?Content
    {
        return new Breakline;
    }
}
