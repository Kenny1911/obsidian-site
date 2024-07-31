<?php

declare(strict_types=1);

namespace App\File\FileType;

use Symfony\Component\Finder\SplFileInfo;

interface FileTypeDetector
{
    public function detect(SplFileInfo $file): FileType;
}
