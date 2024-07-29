<?php

declare(strict_types=1);

namespace App\Controller;

use cebe\markdown\Parser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class ContentController
{
    private readonly string $contentBasePath;

    private readonly string $contentIndex;

    private readonly Parser $markdownParser;

    private readonly UrlGeneratorInterface $urlGenerator;

    private readonly Environment $twig;

    private readonly array $excludePaths;

    public function __construct(
        string $contentBasePath,
        string $contentIndex,
        Parser $markdownParser,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        array $excludePaths = [],
    ) {
        $this->contentBasePath = rtrim(trim($contentBasePath), '/');
        $this->contentIndex = $contentIndex;
        $this->markdownParser = $markdownParser;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->excludePaths = array_map([self::class, 'normalizePath'], $excludePaths);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(string $path): Response
    {
        $path = self::normalizePath($path);

        if ('' === $path) {
            return new RedirectResponse($this->urlGenerator->generate('app.content', ['path' => $this->contentIndex]));
        }

        foreach ($this->excludePaths as $excludePath) {
            if ($path === $excludePath || str_starts_with($path, $excludePath.'/')) {
                throw new NotFoundHttpException('Not found');
            }
        }

        $filePath = $this->contentBasePath.'/'.$path; // Absolute path to file

        if (!file_exists($filePath)) {
            if (file_exists($filePath.'.md')) {
                return new RedirectResponse($this->urlGenerator->generate('app.content', ['path' => $path.'.md']));
            }

            if (!str_contains('/', $path)) {
                $finder = (new Finder())->in($this->contentBasePath)->name([$path, $path.'.md']);

                if (1 === count($finder)) {
                    /** @var SplFileInfo $file */
                    foreach ($finder as $file) {
                        return new RedirectResponse($this->urlGenerator->generate('app.content', ['path' => $file->getRelativePathname()]));
                    }
                }
            }

            throw new NotFoundHttpException('Not found');
        }

        if (self::isMarkdown($filePath)) {
            return new Response(
                $this->twig->render(
                    'content.html.twig',
                    [
                        'title' => pathinfo($filePath)['filename'] ?? null,
                        'content' => $this->markdownParser->parse(file_get_contents($filePath))
                    ]
                )
            );
        }

        return new BinaryFileResponse($filePath);
    }

    private static function normalizePath(string $path): string
    {
        return trim($path, " \n\r\t\v\0/");
    }

    private static function isMarkdown(string $path): bool
    {
        return str_ends_with($path, '.md');
    }
}
