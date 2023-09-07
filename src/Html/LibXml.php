<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html;

use const LIBXML_DOTTED_VERSION;

class LibXml
{
    public function getDottedVersion(): string
    {
        return LIBXML_DOTTED_VERSION;
    }
}
