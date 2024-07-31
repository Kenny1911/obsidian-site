<?php

declare(strict_types=1);

namespace App\File\Finder;

use Symfony\Component\Finder\Finder;

interface FinderFactory
{
    public function create(): Finder;
}
