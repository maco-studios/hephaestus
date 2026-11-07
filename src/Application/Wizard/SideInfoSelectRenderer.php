<?php

declare(strict_types=1);

/**
 * Copyright (c) 2026 Marcos "Marcão" Aurelio
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/hephaestus
 */

namespace Hephaestus\Application\Wizard;

use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Themes\Default\Renderer;

final class SideInfoSelectRenderer extends Renderer
{
    public function __invoke(SelectPrompt $prompt): string|static
    {
        if ($prompt->state !== 'initial' && $prompt->state !== 'active') {
            return (new \Laravel\Prompts\Themes\Default\SelectPromptRenderer($prompt))($prompt);
        }

        $terminalWidth = Prompt::terminal()->cols();
        $description = trim($prompt->infoText());
        if ($description === '') {
            return (new \Laravel\Prompts\Themes\Default\SelectPromptRenderer($prompt))($prompt);
        }

        $sideWidth = min(44, max(24, $this->contentWidth($description) + 4));
        $optionWidth = max(array_map($this->contentWidth(...), $prompt->options));
        $mainWidth = min(
            max(36, $optionWidth + 8),
            max(36, $terminalWidth - $sideWidth - 5),
        );
        $options = $this->options($prompt, $mainWidth - 4);
        $details = $this->wrap($description, $sideWidth - 4);
        $height = max(count($options), count($details));
        $mainLines = $this->box($this->cyan($this->truncate($prompt->label, $mainWidth - 4)), $options, $mainWidth - 4, 'cyan', $height);
        $sideLines = $this->box(' Details ', $details, $sideWidth - 4, 'gray', $height);

        foreach ($mainLines as $index => $line) {
            $this->line($line . ' ' . $sideLines[$index]);
        }

        if ($prompt->hint !== '') {
            $this->hint($prompt->hint);
        }

        return $this;
    }

    /** @return list<string> */
    private function options(SelectPrompt $prompt, int $width): array
    {
        $keys = array_keys($prompt->options);
        $lines = [];
        foreach ($prompt->visible() as $key => $label) {
            $index = array_search($key, $keys, true);
            $active = $index === $prompt->highlighted;
            $prefix = $active ? $this->cyan('› ●') . ' ' : '  ' . $this->dim('○') . ' ';
            $text = $this->truncate((string) $label, max(1, $width - 4));
            $lines[] = $prefix . ($active ? $text : $this->dim($text));
        }

        return $lines;
    }

    /** @param list<string> $lines @return list<string> */
    private function box(string $title, array $lines, int $width, string $color, int $bodyHeight): array
    {
        $width = max(10, $width);
        $lines = array_pad($lines, $bodyHeight, '');
        $plainTitle = preg_replace('/\e\[[;\d]*m/', '', $title) ?? $title;
        $titleLength = mb_strwidth($plainTitle);
        $output = [$this->{$color}(' ┌' . $title . str_repeat('─', max(1, $width - $titleLength - 1)) . '┐')];

        foreach ($lines as $line) {
            $plain = preg_replace('/\e\[[;\d]*m/', '', $line) ?? $line;
            $output[] = $this->{$color}(' │') . ' ' . $line . str_repeat(' ', max(0, $width - mb_strwidth($plain))) . ' ' . $this->{$color}('│');
        }

        $output[] = $this->{$color}(' └' . str_repeat('─', $width + 2) . '┘');

        return $output;
    }

    /** @return list<string> */
    private function wrap(string $text, int $width): array
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        return $text === '' ? [''] : explode("\n", wordwrap($text, max(1, $width), "\n", true));
    }

    private function contentWidth(string $text): int
    {
        return mb_strwidth(preg_replace('/\e\[[;\d]*m/', '', $text) ?? $text);
    }
}
