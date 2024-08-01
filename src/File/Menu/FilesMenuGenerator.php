<?php

declare(strict_types=1);

namespace App\File\Menu;

interface FilesMenuGenerator
{
    /**
     * @return list<Item>
     */
    public function generate(?string $activePath = null): array;
}
