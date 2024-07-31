<?php

declare(strict_types=1);

namespace App\File;

use App\File\FileType\FileType;
use App\File\FileType\FileTypeDetector;
use App\File\FileType\MainFileTypeDetector;
use App\File\Finder\FinderFactory;
use App\File\Finder\MainFinderFactory;
use App\File\PathNormalizer\PathNormalizer;
use App\File\PathNormalizer\WhitespacePathNormalizer;
use LogicException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Facade of other file services.
 */
final class FileUtil
{
    private readonly PathNormalizer $pathNormalizer;

    private readonly FinderFactory $finderFactory;

    private readonly FileTypeDetector $fileTypeDetector;

    public function __construct(
        PathNormalizer $pathNormalizer,
        FinderFactory $finderFactory,
        FileTypeDetector $fileTypeDetector,
    ) {
        $this->pathNormalizer = $pathNormalizer;
        $this->finderFactory = $finderFactory;
        $this->fileTypeDetector = $fileTypeDetector;
    }

    /**
     * @param array<string> $excludePaths
     * @param array<string> $markdownExtensions
     */
    public static function createFromSettings(
        string $basePath,
        array $excludePaths = [],
        array $markdownExtensions = ['md'],
    ): self {
        $pathNormalizer = new WhitespacePathNormalizer();
        $isBasePathAbsolute = str_starts_with($basePath, '/');

        return new self(
            $pathNormalizer,
            new MainFinderFactory(
                ($isBasePathAbsolute ? '/' : '') .$pathNormalizer->normalize($basePath),
                array_values(array_filter(array_map([$pathNormalizer, 'normalize'], $excludePaths))),
            ),
            new MainFileTypeDetector(array_values(array_filter($markdownExtensions))),
        );
    }

    public function createFinder(): Finder
    {
        return $this->finderFactory->create();
    }

    public function getFile(string $path): ?SplFileInfo
    {
        return $this->getFirstResult(
            $this->createFinder()
                ->path($this->dirname($path))
                ->name($this->filename($path))
        );
    }

    public function getFirstResult(Finder $finder): ?SplFileInfo
    {
        return iterator_to_array($finder, false)[0] ?? null;
    }

    public function getFileType(SplFileInfo|string $file): FileType
    {
        if (is_string($file)) {
            $file = $this->getFile($file) ?? throw new LogicException('File not found.');
        }

        return $this->fileTypeDetector->detect($file);
    }

    public function normalizePath(string $path): string
    {
        return $this->pathNormalizer->normalize($path);
    }

    public function dirname(string $path): string
    {
        return $this->normalizePath(dirname($path));
    }

    public function filename(string $path): string
    {
        return $this->normalizePath(basename($path));
    }
}
