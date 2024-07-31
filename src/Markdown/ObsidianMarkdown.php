<?php

declare(strict_types=1);

namespace App\Markdown;

use App\Routing\UrlGenerator;
use cebe\markdown\Markdown;

/**
 * Markdown parser for Obsidian
 */
final class ObsidianMarkdown extends Markdown
{
    private readonly UrlGenerator $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    protected function consumeHeadline($lines, $current): array
    {
        $headline = parent::consumeHeadline($lines, $current);

        $id = self::prepareId($lines[$current]);

        $headline[0]['id'] = $id;

        return $headline;
    }

    /**
     * Renders a headline
     */
    protected function renderHeadline($block): string
    {
        if (isset($block['id'])) {
            $id = (string) $block['id'];
            $tag = 'h' . $block['level'];

            return "<$tag id=\"{$id}\">" . $this->renderAbsy($block['content']) . "</$tag>\n";
        }

        return parent::renderHeadline($block);
    }

    /**
     * Parse Obsidian block anchor, indicated by `^`
     * @marker ^
     */
    protected function parseObsidianBlockAnchor(string $markdown): array
    {
        return [['obsidianBlockAnchor', 'id' => $markdown], mb_strlen($markdown)];
    }

    protected function renderObsidianBlockAnchor($block): string
    {
        return "<span id=\"{$block['id']}\"></span>";
    }

    /**
     * Parse Obsidian link, indicated by `[[`.
     * @marker [[
     */
    protected function parseObsidianLink(string $markdown): array
    {
        if (false === ($strpos = mb_strpos($markdown, ']]'))) {
            return [['text', $markdown], strlen($markdown)];
        }

        $markdown = mb_substr($markdown, 0, $strpos + 2);
        //$markdown = explode(']]', $markdown, 2)[0] . ']]';

        $parts = explode(
            '|',
            rtrim(
                ltrim($markdown, '['),
                ']'
            ),
            2,
        );

        if (0 === count($parts)) {
            return [['text', $markdown], strlen($markdown)];
        }

        [$path, $anchor] = explode('#', $parts[0], 2) + ['', null];
        $path = trim($path);
        $anchor = $anchor ? trim($anchor) : null;
        $text = strip_tags(trim($parts[1] ?? $parts[0]));

        return [
            [
                'link',
                'text' => [['text', $text]],
                'url' => $this->urlGenerator->generate($path, $anchor),
                'title' => $text,
            ],
            strlen($markdown),
        ];
    }

    protected function consumeParagraph($lines, $current): array
    {
        $block = parent::consumeParagraph($lines, $current);

        // Move obsidianBlockAnchor to top
        $content = $block[0]['content'] ?? null;

        if (!is_array($content)) {
            return $block;
        }

        usort($content, function(array $a, array $b) {
            if ($a[0] !== $b[0]) {
                if ($a[0] === 'obsidianBlockAnchor') {
                    return -1;
                }

                if ($b[0] === 'obsidianBlockAnchor') {
                    return 1;
                }
            }

            return 0;
        });

        $block[0]['content'] = $content;

        return $block;
    }

    private static function prepareId(string $text): string
    {
        $text = str_replace(['#', '^', '|'. '\\', ':'], ' ', $text);
        $text = (string) preg_replace('/\s+/', ' ', $text);
        $text = htmlspecialchars($text);

        return trim($text);
    }
}
