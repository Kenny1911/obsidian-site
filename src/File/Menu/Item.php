<?php

declare(strict_types=1);

namespace App\File\Menu;

abstract class Item
{
    public readonly string $title;

    public readonly string $path;

    public readonly string $relativePath;

    public bool $isActive;

    public function __construct(string $title, string $path, string $relativePath, bool $isActive)
    {
        $this->title = $title;
        $this->path = $path;
        $this->relativePath = $relativePath;
        $this->isActive = $isActive;
    }

    abstract public function isFile(): bool;

    abstract public function isDir(): bool;
}
