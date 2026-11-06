<?php

declare(strict_types=1);

/**
 * Copyright (c) 2026 Marcos "Marcão" Aurelio
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/mage-os-installer
 */

namespace MageOsInstaller\Application\Wizard;

use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Themes\Default\Concerns\DrawsScrollbars;
use Laravel\Prompts\Themes\Default\MultiSelectPromptRenderer;
use Laravel\Prompts\Themes\Default\Renderer;

final class SideInfoMultiSelectRenderer extends Renderer
{
    use DrawsScrollbars;

    public function __invoke(MultiSelectPrompt $prompt)
    {
        if ($prompt->state !== 'initial' && $prompt->state !== 'active') {
            return (new MultiSelectPromptRenderer($prompt))($prompt);
        }

        $terminalWidth = Prompt::terminal()->cols();
        $description = trim($prompt->infoText());
        if ($description === '') {
            return (new MultiSelectPromptRenderer($prompt))($prompt);
        }

        $sideWidth = min(44, max(24, $this->contentWidth($description) + 4));
        $optionWidth = max(array_map(fn($option) => $this->contentWidth((string) $option), $prompt->options));
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
    private function options(MultiSelectPrompt $prompt, int $width): array
    {
        $visible = $prompt->visible();
        $keys = array_keys($prompt->options);
        $lines = [];

        foreach ($visible as $key => $label) {
            $index = array_search($key, $keys, true);
            $value = array_is_list($prompt->options) ? $prompt->options[$index] : $key;
            $active = $index === $prompt->highlighted;
            $selected = in_array($value, $prompt->value(), true);
            $prefix = match (true) {
                $active && $selected => $this->cyan('› ◼') . ' ',
                $active => $this->cyan('›') . ' ◻ ',
                $selected => '  ' . $this->cyan('◼') . ' ',
                default => '  ' . $this->dim('◻') . ' ',
            };
            $available = max(1, $width - 4);
            $text = $this->truncate((string) $label, $available);
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
        $top = ' ┌' . $title . str_repeat('─', max(1, $width - $titleLength - 1)) . '┐';
        $output = [$this->{$color}($top)];

        foreach ($lines as $line) {
            $plain = preg_replace('/\e\[[;\d]*m/', '', $line) ?? $line;
            $padding = max(0, $width - mb_strwidth($plain));
            $output[] = $this->{$color}(' │') . ' ' . $line . str_repeat(' ', $padding) . ' ' . $this->{$color}('│');
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
