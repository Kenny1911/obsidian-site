<?php

declare(strict_types=1);

namespace App\File\Finder;

use Symfony\Component\Finder\Finder;

final class MainFinderFactory implements FinderFactory
{
    private readonly string $basePath;

    /** @var list<string> */
    private readonly array $excludePaths;

    /**
     * @param list<string> $excludePaths
     */
    public function __construct(string $basePath, array $excludePaths)
    {
        $this->basePath = $basePath;
        $this->excludePaths = $excludePaths;
    }

    public function create(): Finder
    {
        return (new Finder())
            ->in($this->basePath)
            ->notPath($this->excludePaths)
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
        ;
    }
}
