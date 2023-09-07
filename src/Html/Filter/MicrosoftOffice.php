<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function str_replace;
use function strpos;

use const LIBXML_DOTTED_VERSION;

class MicrosoftOffice extends Filter
{
    public function preProcess(string $text): string
    {
        if (! $this->isOfficeDocument($text)) {
            return $text;
        }

        $text = $this->removeNamespacedParagraphs($text);

        return $this->fixConditionalComments($text);
    }

    private function isOfficeDocument(string $html): bool
    {
        return strpos($html, 'urn:schemas-microsoft-com:office') !== false
            // Microsoft 12 and earlier do not include an xmlns schema.
            || strpos($html, '<meta name="Generator" content="Microsoft Word');
    }

    private function removeNamespacedParagraphs(string $html): string
    {
        return str_replace(['<o:p>', '</o:p>'], '', $html);
    }

    private function fixConditionalComments(string $html): string
    {
        if (LIBXML_DOTTED_VERSION === '2.9.14') {
            $html = preg_replace('/<!(\[if\s*[^\[\]]+\])>/', '<!--$1-->', $html);
            $html = preg_replace('/<!(\[endif\])>/', '<!--$1-->', $html);
        }

        return $html;
    }
}
