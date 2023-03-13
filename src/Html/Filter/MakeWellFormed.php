<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use SupportPal\DomUtils\Html\Tidy;

/**
 * masterminds/html5-php has some quirks where it doesn't handle the same as DOMDocument.
 *
 * This fixes those instances:
 *  - content before html/body
 *  - missing <head> or <body> elements
 */
class MakeWellFormed extends Filter
{
    private Tidy $tidy;

    public function __construct()
    {
        $this->tidy = new Tidy;
    }

    public function preProcess(string $text): string
    {
        $this->tidy->loadHtml($text);

        return $this->tidy->saveHtml();
    }
}
