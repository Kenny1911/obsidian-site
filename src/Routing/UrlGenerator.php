<?php

declare(strict_types=1);

namespace App\Routing;

interface UrlGenerator
{
    public function generate(string $path, ?string $anchor = null): string;
}
