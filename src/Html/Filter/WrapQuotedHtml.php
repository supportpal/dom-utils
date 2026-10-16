<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use SupportPal\DomUtils\DOMDocument;

use function count;
use function in_array;
use function strcmp;
use function strtolower;
use function trim;

class WrapQuotedHtml extends Filter
{
    /**
     * DOMXPath expressions for quoted elements.
     *
     * @var array<int, string>
     */
    private array $quoted_elements = [
        // Standard quote block tag
        '//blockquote',
        // Thunderbird
        '//div[contains(@class,"moz-cite-prefix")]',
        '//pre[contains(@class,"moz-signature")]',   // Plain text signature in HTML e-mail.
        // Gmail
        '//div[contains(@class,"gmail_extra")]',
        '//div[contains(@class,"gmail_quote")]',
        '//div[contains(@class,"gmail_signature")]',
        // Yahoo
        '//div[contains(@class,"yahoo_quoted")]',
        // Outlook
        '//div[contains(@id,"Signature")]',
        '//hr[contains(@id,"stopSpelling")]',
        //      Careful with this one... the units of measure can change e.g. 1.0pt, 1pt, 0cm, 0in - many variations!
        '//div[contains(@style,"border:none") and contains(@style,"border-top:solid #E1E1E1 1") and contains(@style,"padding:3")]',
        '//div[contains(@style,"border:none") and contains(@style,"border-top:solid #B5C4DF 1") and contains(@style,"padding:3")]',
        // Zimbra Web Client
        '//hr[contains(@id,"zwchr")]',
        // Web.de
        '//div[contains(@name,"quote")]',
        // Airmail
        '//p[contains(@class,"gmail_quote")]',
        '//p[contains(@class,"airmail_on")]',
        '//div[contains(@class,"airmail_ext_on")]',
        // Postmail
        '//div[contains(@class,"moz-signature")]',
        // Acompli
        '//div[contains(@id,"divRplyFwdMsg")]',
        // Applemail
        '//div[contains(@id,"AppleMailSignature")]',
        // Mailbird
        '//div[contains(@class,"mb_sig")]',
    ];

    public function preProcess(string $text): string
    {
        return $this->hideQuotedHtml($text);
    }

    /**
     * Wrap quoted text in an expandable tag.
     */
    private function hideQuotedHtml(string $html): string
    {
        // Create DOMDocument.
        $dom = (new DOMDocument)->loadHTML($html);
        $xp = new DOMXPath($dom->getInstance());

        // List of quoted nodes.
        $nodes = [];

        // Attempt to find elements that should be hidden.
        foreach ($this->quoted_elements as $expression) {
            // Search for element matching the expression.
            $nodeList = $xp->query($expression);
            if ($nodeList === false || $nodeList->length === 0) {
                continue;
            }

            $node = $nodeList->item(0);
            if (! $node instanceof DOMNode) {
                continue;
            }

            // Skip the node if it and its siblings contain no visible text.
            if (! $this->hasVisibleText($node)) {
                continue;
            }

            $nodes[$expression] = $node;
        }

        // Find out which of the quoted nodes occurs first in the DOM.
        if (count($nodes) > 0) {
            $node = $this->getShortestPath($nodes);
            if ($node?->parentNode === null) {
                return $html;
            }

            // Insert div.expandable before the element.
            $expandable = $dom->createElement('div', '');
            $expandable->setAttribute('class', 'expandable');
            $node->parentNode->insertBefore($expandable, $node);

            // Wrap the element in a div.hide-quoted-text element.
            $hideText = $dom->createElement('div');
            $hideText->setAttribute('class', 'supportpal_quote');
            $hideText->appendChild($node->cloneNode(true));

            // Move all siblings of element into the wrapper.
            $nextSibling = $node->nextSibling;
            while ($nextSibling) {
                $hideText->appendChild($nextSibling->cloneNode(true));
                $oldSibling = $nextSibling;
                $nextSibling = $nextSibling->nextSibling;
                if ($oldSibling->parentNode === null) {
                    continue;
                }

                $oldSibling->parentNode->removeChild($oldSibling);
            }

            // Add it before the quoted text begins
            $node->parentNode->insertBefore($hideText, $node);

            // Remove the original node (we cloned it earlier).
            $node->parentNode->removeChild($node);

            return ($savedHtml = $dom->saveHTML()) !== false ? $savedHtml : $html;
        }

        // Something weird happened, don't modify the HTML.
        return $html;
    }

    /**
     * Whether the node, or any of its following siblings, contain human readable text.
     */
    private function hasVisibleText(?DOMNode $node): bool
    {
        for (; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof DOMText) {
                if (trim($node->nodeValue ?? '', " \n\r\t\0\xC2\xA0") !== '') {
                    return true;
                }

                continue;
            }

            // We only care about human readable (visible) text.
            if ($node instanceof DOMElement && in_array(strtolower($node->tagName), ['head', 'style', 'script'], true)) {
                continue;
            }

            if ($node->hasChildNodes() && $this->hasVisibleText($node->firstChild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the node with the shortest path.
     *
     * @param array<string|int, DOMNode> $comparison
     */
    private function getShortestPath(array $comparison): ?DOMNode
    {
        $firstNode = null;
        foreach ($comparison as $node) {
            // Get the node position path
            $nodePath = $node->getNodePath();

            // Update if this node comes earlier in the document
            if ($nodePath === null
                || (($firstNodePath = $firstNode?->getNodePath()) !== null && strcmp($nodePath, $firstNodePath) >= 0)
            ) {
                continue;
            }

            $firstNode = $node;
        }

        return $firstNode;
    }
}
