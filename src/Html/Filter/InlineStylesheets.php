<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use SupportPal\DomUtils\DOMDocument;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Convert <style> blocks to inline style attributes.
 *
 * <style> is not permitted in <body> in HTML5 https://html.spec.whatwg.org/multipage/semantics.html#the-style-element
 *
 * The most robust way to email CSS is via the style attribute, see https://www.caniemail.com/
 * HTMLPurifier also supports sanitizing the style attribute. The only negative of this approach is that we cannot
 * permit @ css rules (media queries, font faces). That being said, HTMLPurifier cannot sanitize these rules and
 * email client support varies a lot so I don't think there is demand for this right now.
 */
class InlineStylesheets extends Filter
{
    public function postProcess(string $text): string
    {
        $cssToInlineStyles = new CssToInlineStyles(true);
        $cssToInlineStyles->setLibXmlOptions(DOMDocument::LIBXML_OPTS);

        return $cssToInlineStyles->convert($text);
    }
}
