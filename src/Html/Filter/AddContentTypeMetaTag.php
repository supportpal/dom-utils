<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use function preg_replace;

/**
 * Add HTML4 <meta> charset definition.
 *
 * Documents that don't define a charset default to ISO-8859-1 and this results in characters getting mangled when
 * running saveHtml on a DOMDocument instance. We have to use HTML4 <meta> definition as DOMDocument does not
 * support HTML5.
 *
 * Similar to
 *   - https://github.com/MyIntervals/emogrifier/blob/92c1121fbef468405bb54b22a1c37d4d322d6051/src/HtmlProcessor/AbstractHtmlProcessor.php#L312
 *   - https://github.com/roundcube/roundcubemail/blob/26a194859762e4d53f8894f92e6610c06c11f3d5/program/actions/mail/index.php#L909
 */
class AddContentTypeMetaTag extends Filter
{
    public function preProcess(string $text): string
    {
        // https://www.php.net/manual/en/domdocument.loadhtml.php#118834
        $meta = '<meta http-equiv="content-type" content="text/html;charset=utf-8">';

        // Remove existing <meta charset> tags.
        $html = preg_replace('/<meta[^>]+charset=[a-z0-9_"-]+[^>]*>\s*?/Ui', '', $text, -1, $count);
        if ($html !== null && $count) {
            $text = $html;
        }

        // Add <meta> tag to <head>.
        $html = preg_replace('/(<head[^>]*>)/Ui', '\\1'.$meta, $text, -1, $count);
        if ($html !== null && $count) {
            $text = $html;
        }

        return $text;
    }
}
