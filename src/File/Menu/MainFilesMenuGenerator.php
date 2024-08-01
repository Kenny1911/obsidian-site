<?php

declare(strict_types=1);

namespace App\File\Menu;

use App\File\FileType\FileType;
use App\File\FileUtil;
use App\Routing\UrlGenerator;
use LogicException;
use Symfony\Component\Finder\SplFileInfo;

final class MainFilesMenuGenerator implements FilesMenuGenerator
{
    private readonly FileUtil $fileUtil;

    private readonly UrlGenerator $urlGenerator;

    public function __construct(FileUtil $fileUtil, UrlGenerator $urlGenerator)
    {
        $this->fileUtil = $fileUtil;
        $this->urlGenerator = $urlGenerator;
    }

    public function generate(?string $activePath = null): array
    {
        $activePath = $activePath ? $this->fileUtil->normalizePath($activePath) : null;
        
        return $this->getListInDir('', $activePath);
    }
    
    /**
     * @return list<Item>
     */
    private function getListInDir(string $inDir = '', ?string $activePath = null): array
    {
        $items = [];
        $inDir = $this->fileUtil->normalizePath($inDir);
        $depth = '' === $inDir ? 0 : (substr_count($inDir, '/') + 1);
        
        $finder = $this->fileUtil
            ->createFinder()
            ->depth($depth)
            ->sort(function (SplFileInfo $a, SplFileInfo $b): int {
                if ($a->isDir() && !$b->isDir()) {
                    return -1;
                }

                if (!$a->isDir() && $b->isDir()) {
                    return 1;
                }

                return strcmp($a->getFilename(), $b->getFilename());
            })
        ;
        
        if ($inDir) {
            $finder->path($inDir.'/');
        }
        
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $items[] = $this->createItem($file, $activePath);
        }
        
        return $items;
    }
    
    private function createItem(SplFileInfo $file, ?string $activePath = null): Item
    {
        $relativePathname = $this->fileUtil->normalizePath($file->getRelativePathname());

        if ($file->isFile()) {
            $fileType = $this->fileUtil->getFileType($file);

            return new FileItem(
                FileType::MARKDOWN === $fileType ? $file->getFilenameWithoutExtension() : $file->getFilename(),
                $file->getRealPath(),
                $relativePathname,
                $activePath === $relativePathname,
                $this->urlGenerator->generate($relativePathname),
                $fileType,
            );
        } elseif ($file->isDir()) {
            return new DirectoryItem(
                $file->getFilename(),
                $file->getRealPath(),
                $relativePathname,
                $activePath && str_starts_with($activePath, $relativePathname.'/'),
                $this->getListInDir($relativePathname, $activePath),
            );
        }

        throw new LogicException('Invalid file.');
    }
}
