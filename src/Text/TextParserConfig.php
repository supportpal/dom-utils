<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Text;

class TextParserConfig
{
    private int $margin = 2;

    public function setBlockElementMargin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getBlockElementMargin(): int
    {
        return $this->margin;
    }
}
