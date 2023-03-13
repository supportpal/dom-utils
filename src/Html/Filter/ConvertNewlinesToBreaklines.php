<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function nl2br;

class ConvertNewlinesToBreaklines extends Filter
{
    public function preProcess(string $text): string
    {
        return nl2br($text);
    }
}
