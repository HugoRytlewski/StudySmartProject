<?php

namespace App\Service;

use League\CommonMark\CommonMarkConverter;

class MarkdownParser
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter();
    }

    public function toHtml(string $markdown): string
    {
        return $this->converter->convert($markdown);
    }
}
