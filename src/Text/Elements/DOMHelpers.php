<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Elements;

use DOMElement;
use DOMNode;

trait DOMHelpers
{
    public function previousElementSibling(DOMNode $node): ?DOMElement
    {
        $node = $node->previousSibling;
        while ($node !== null) {
            if ($node instanceof DOMElement) {
                return $node;
            }

            $node = $node->previousSibling;
        }

        return null;
    }

    public function nextElementSibling(DOMNode $node): ?DOMElement
    {
        $node = $node->nextSibling;
        while ($node !== null) {
            if ($node instanceof DOMElement) {
                return $node;
            }

            $node = $node->nextSibling;
        }

        return null;
    }

    public function firstChildElement(DOMNode $node): ?DOMElement
    {
        $i = 0;
        while ($child = $node->childNodes->item($i++)) {
            if ($child instanceof DOMElement) {
                return $child;
            }
        }

        return null;
    }
}
