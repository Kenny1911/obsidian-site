<?php

declare(strict_types=1);

namespace App\File\Menu;

final class DirectoryItem extends Item
{
    /** @var list<Item> */
    public readonly array $children;

    /**
     * @param array<Item> $children
     */
    public function __construct(string $title, string $path, string $relativePath, bool $isActive, array $children)
    {
        parent::__construct($title, $path, $relativePath, $isActive);

        $this->children = array_values($children);
    }

    public function isFile(): bool
    {
        return false;
    }

    public function isDir(): bool
    {
        return true;
    }
}
