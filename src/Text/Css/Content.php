<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Css;

use Stringable;

class Content implements Stringable
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
