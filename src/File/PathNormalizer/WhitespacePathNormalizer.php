<?php

declare(strict_types=1);

namespace App\File\PathNormalizer;

use LogicException;

/**
 * @see https://github.com/thephpleague/flysystem
 */
final class WhitespacePathNormalizer implements PathNormalizer
{
    public function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $this->rejectFunkyWhiteSpace($path);

        return $this->normalizeRelativePath($path);
    }

    private function rejectFunkyWhiteSpace(string $path): void
    {
        if (preg_match('#\p{C}+#u', $path)) {
            throw new LogicException('Corrupted path detected: '.$path);
        }
    }

    private function normalizeRelativePath(string $path): string
    {
        $parts = [];

        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;

                case '..':
                    if (empty($parts)) {
                        throw new LogicException('Path traversal detected: '.$path);
                    }
                    array_pop($parts);
                    break;

                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }
}