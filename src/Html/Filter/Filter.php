<?php declare(strict_types=1);

namespace SupportPal\DomUtils\Html\Filter;

abstract class Filter
{
    public function preProcess(string $text): string
    {
        return $text;
    }

    public function postProcess(string $text): string
    {
        return $text;
    }
}
