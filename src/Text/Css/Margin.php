<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text\Css;

use function str_repeat;

class Margin extends Content
{
    private int $number;

    public function __construct(int $number)
    {
        $this->number = $number;
        $content = str_repeat("\n", $number);

        parent::__construct($content);
    }

    public function prefixContent(string $content): self
    {
        $this->content = $content . $this->content;

        return $this;
    }

    public function appendContent(string $content): self
    {
        $this->content .= $content;

        return $this;
    }

    public function getMargin(): int
    {
        return $this->number;
    }
}
