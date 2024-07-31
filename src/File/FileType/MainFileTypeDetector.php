<?php

declare(strict_types=1);

namespace App\File\FileType;

use Symfony\Component\Finder\SplFileInfo;

final class MainFileTypeDetector implements FileTypeDetector
{
    /** @var list<string> */
    private readonly array $markdownExtensions;

    /**
     * @param list<string> $markdownExtensions
     */
    public function __construct(array $markdownExtensions)
    {
        $this->markdownExtensions = $markdownExtensions;
    }

    public function detect(SplFileInfo $file): FileType
    {
        if (in_array($file->getExtension(), $this->markdownExtensions, true)) {
            return FileType::MARKDOWN;
        }

        return FileType::OTHER;
    }
}
