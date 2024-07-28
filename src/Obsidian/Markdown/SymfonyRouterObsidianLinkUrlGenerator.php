<?php

declare(strict_types=1);

namespace App\Obsidian\Markdown;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SymfonyRouterObsidianLinkUrlGenerator implements ObsidianLinkUrlGenerator
{
    private readonly UrlGeneratorInterface $urlGenerator;

    private readonly string $routeName;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $routeName)
    {
        $this->urlGenerator = $urlGenerator;
        $this->routeName = $routeName;
    }

    public function generate(string $path, ?string $anchor = null): string
    {
        $params = ['path' => $path];

        if ($anchor) {
            $params['_fragment'] = $anchor;
        }

        return $this->urlGenerator->generate($this->routeName, $params);
    }
}
