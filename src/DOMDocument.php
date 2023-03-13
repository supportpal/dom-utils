<?php declare(strict_types=1);

namespace SupportPal\DomUtils;

use DOMNode;
use Exception;
use Masterminds\HTML5;
use RuntimeException;
use SupportPal\DomUtils\Html\Filter\AddContentTypeMetaTag;
use SupportPal\DomUtils\Html\Filter\MakeWellFormed;
use SupportPal\DomUtils\Html\Filter\Trim;
use SupportPal\DomUtils\Html\Html;

use function call_user_func_array;
use function libxml_use_internal_errors;
use function method_exists;
use function property_exists;
use function sprintf;

use const LIBXML_COMPACT;
use const LIBXML_PARSEHUGE;

/**
 * @mixin HTML5
 * @mixin \DOMDocument
 */
class DOMDocument
{
    const LIBXML_OPTS = LIBXML_PARSEHUGE | LIBXML_COMPACT;

    protected ?HTML5 $html5;

    protected \DOMDocument $domDocument;

    /**
     * DOMDocument constructor.
     *
     * @param  string  $version   The version number of the document as part of the XML declaration.
     * @param  string  $encoding  The encoding of the document as part of the XML declaration.
     */
    public function __construct(string $version = '1.0', string $encoding = 'UTF-8')
    {
        // Constructor arguments are overridden when loadHtml is used but useful if you're manually creating documents.
        $this->domDocument = new \DOMDocument($version, $encoding);
    }

    /**
     * Load DOMDocument.
     *
     * @param  string $string
     * @return static
     */
    public function loadHTML(string $string)
    {
        $html = new Html($string);
        $string = $html->filter([Trim::class, MakeWellFormed::class, AddContentTypeMetaTag::class])->getHtml();

        if ($html->isHtml5()) {
            try {
                $this->domDocument = $this->parseHtml5($string);

                return $this;
            } catch (Exception $e) {
                $this->html5 = null;
            }
        }

        // Use DOMDocument for all else.
        $this->parseXhtml($string);

        return $this;
    }

    /**
     * Dumps the internal document into a string using HTML formatting.
     *
     * @param  DOMNode $dom [optional] parameter to output a subset of the document.
     * @return string|false
     */
    public function saveHTML(?DOMNode $dom = null)
    {
        if (isset($this->html5)) {
            if ($dom === null) {
                $dom = $this->domDocument;
            }

            return $this->html5->saveHTML($dom);
        }

        // Fall back to DOMDocument.
        return $this->domDocument->saveHTML($dom);
    }

    /**
     * Get base DOMDocument instance.
     */
    public function getInstance(): \DOMDocument
    {
        return $this->domDocument;
    }

    /**
     * Dynamically retrieve DOMDocument property.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this->domDocument, $name)) {
            return $this->domDocument->$name;
        }

        throw new RuntimeException(sprintf('Property \'%s\' not defined.', $name));
    }

    /**
     * Dynamically set DOMDocument property.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->domDocument->$name = $value;
    }

    /**
     * Determine if a property is set on the DOMDocument.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->domDocument->$key);
    }

    /**
     * Unset a property on the DOMDocument.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset(string $key)
    {
        unset($this->domDocument->$key);
    }

    /**
     * Call a HTML5 or DOMDocument instance method.
     *
     * @param  string $name
     * @param  array<int|string, mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (isset($this->html5) && method_exists($this->html5, $name)) {
            return $this->html5->$name($arguments);
        }

        if (method_exists($this->domDocument, $name)) {
            /** @var callable $callable */
            $callable = [$this->domDocument, $name];

            return call_user_func_array($callable, $arguments);
        }

        throw new RuntimeException(sprintf('Method \'%s\' not found.', $name));
    }

    /**
     * Parse a HTML5 document.
     *
     * @param  string $htmlContent
     * @return \DOMDocument
     */
    protected function parseHtml5(string $htmlContent): \DOMDocument
    {
        $this->html5 = new HTML5(['disable_html_ns' => true]);

        return $this->html5->parse($htmlContent);
    }

    /**
     * Parse an XHTML document.
     *
     * @param  string $htmlContent
     * @return $this
     */
    protected function parseXhtml(string $htmlContent)
    {
        $internalErrors = libxml_use_internal_errors(true);

        // LIBXML_PARSEHUGE - relaxes hardcoded limit from the parser. This affects limits like maximum depth of a
        //                    document or the entity recursion, as well as limits of the size of text nodes.
        //                    libxml >= 2.7.0
        // LIBXML_COMPACT   - Activate small nodes allocation optimization (may offer speed boost), libxml >= 2.6.21
        @ $this->domDocument->loadHTML($htmlContent, self::LIBXML_OPTS);

        libxml_use_internal_errors($internalErrors);

        return $this;
    }
}
