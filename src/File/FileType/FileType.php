<?php

declare(strict_types=1);

namespace App\File\FileType;

enum FileType
{
    case MARKDOWN;
    case CANVAS;
    case OTHER;
}
