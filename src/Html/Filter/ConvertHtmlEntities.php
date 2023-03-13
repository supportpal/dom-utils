<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function htmlspecialchars;

use const ENT_QUOTES;

class ConvertHtmlEntities extends Filter
{
    public function preProcess(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
