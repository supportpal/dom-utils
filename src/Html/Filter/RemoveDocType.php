<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function preg_replace;
use function trim;

/**
 * Remove <!DOCTYPE> from the document.
 */
class RemoveDocType extends Filter
{
    public function postProcess(string $text): string
    {
        $html = preg_replace('/<!doctype[^>]+>/im', '', $text);
        if ($html !== null) {
            $text = trim($html);
        }

        return $text;
    }
}
