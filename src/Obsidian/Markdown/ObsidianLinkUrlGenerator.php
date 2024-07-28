<?php

declare(strict_types=1);

namespace App\Obsidian\Markdown;

interface ObsidianLinkUrlGenerator
{
    public function generate(string $path, ?string $anchor = null): string;
}
