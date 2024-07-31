<?php

declare(strict_types=1);

namespace App\File\PathNormalizer;

/**
 * @see https://github.com/thephpleague/flysystem
 */
interface PathNormalizer
{
    public function normalize(string $path): string;
}
