<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function trim;

/**
 * Strip whitespace (or other characters) from the beginning and end of the document.
 */
class Trim extends Filter
{
    public function preProcess(string $text): string
    {
        return trim($text);
    }
}
