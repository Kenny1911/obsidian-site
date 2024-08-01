<?php

declare(strict_types=1);

namespace App\Controller\Content;

use App\File\FileType\FileType;
use App\File\FileUtil;
use App\File\Menu\FilesMenuGenerator;
use App\Routing\UrlGenerator;
use cebe\markdown\Parser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class Controller
{
    private readonly FileUtil $fileUtil;

    private readonly UrlGenerator $urlGenerator;

    private readonly Parser $markdownParser;

    private readonly Environment $twig;

    private readonly FilesMenuGenerator $filesMenuGenerator;

    private readonly string $indexPath;

    /** @var list<string> */
    private readonly array $markdownExtensions;

    /**
     * @param array<string> $markdownExtensions
     */
    public function __construct(
        FileUtil $fileUtil,
        UrlGenerator $urlGenerator,
        Parser $markdownParser,
        Environment $twig,
        FilesMenuGenerator $filesMenuGenerator,
        string $indexPath,
        array $markdownExtensions = ['md'],
    ) {
        $this->fileUtil = $fileUtil;
        $this->urlGenerator = $urlGenerator;
        $this->markdownParser = $markdownParser;
        $this->twig = $twig;
        $this->filesMenuGenerator = $filesMenuGenerator;
        $this->indexPath = $this->fileUtil->normalizePath($indexPath);
        $this->markdownExtensions = array_values(array_filter($markdownExtensions));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(string $path): Response
    {
        $path = $this->fileUtil->normalizePath($path);

        // Redirect to index, if path not set
        if ('' === $path) {
            return new RedirectResponse($this->urlGenerator->generate($this->indexPath));
        }

        // Search file
        $file = $this->fileUtil->getFile($path)
            // Search file without ext
            ?? $this->fileUtil->getFirstResult(
                $this->fileUtil->createFinder()
                    ->path($this->fileUtil->dirname($path))
                    ->name(
                        array_map(
                            fn(string $ext): string => $this->fileUtil->filename($path).'.'.$ext,
                            $this->markdownExtensions
                        )
                    )
            )
            // Search file by name in root dir
            ?? $this->fileUtil->getFirstResult($this->fileUtil->createFinder()->name($path))
            // Search file by name without ext in root
            ?? $this->fileUtil->getFirstResult(
                $this->fileUtil->createFinder()
                    ->name(
                        array_map(
                            fn(string $ext): string => $path.'.'.$ext,
                            $this->markdownExtensions
                        )
                    )
            )
        ;

        if (!$file || !$file->isFile()) {
            throw new NotFoundHttpException('Not found');
        }

        // Redirect to original file
        if ($file->getRelativePathname() !== $path) {
            return new RedirectResponse($this->urlGenerator->generate($file->getRelativePathname()));
        }

        $fileType = $this->fileUtil->getFileType($file);

        if (FileType::MARKDOWN === $fileType) {
            return new Response(
                $this->twig->render(
                    'content.html.twig',
                    [
                        'title' => $file->getFilename(),
                        'content' => $this->markdownParser->parse($file->getContents()),
                        'filesMenu' => $this->filesMenuGenerator->generate($path),
                    ]
                )
            );
        }

        return new BinaryFileResponse($file->getPathname());
    }
}
