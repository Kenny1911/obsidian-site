<?php

declare(strict_types=1);

namespace App\File\Menu;

use App\File\FileType\FileType;

class FileItem extends Item
{
    public readonly string $url;

    public readonly FileType $type;

    public function __construct(string $title, string $path, string $relativePath, bool $isActive, string $url, FileType $type)
    {
        parent::__construct($title, $path, $relativePath, $isActive);

        $this->url = $url;
        $this->type = $type;
    }

    public function isFile(): bool
    {
        return true;
    }

    public function isDir(): bool
    {
        return false;
    }
}