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

namespace Hephaestus\Presentation\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class GenerationScreen
{
    /** @var list<string> */
    private array $output = [];

    private string $stage = 'Preparing generation';

    private int $renderedLines = 0;

    private int $animationFrame = 0;

    public function __construct(
        private readonly OutputInterface $outputWriter,
        private readonly int $maxOutputLines = 8,
        private readonly ?string $target = null,
    ) {}

    public function start(): void
    {
        if (!$this->supportsRedraw()) {
            $this->outputWriter->writeln('<fg=cyan;options=bold>Creating Mage-OS project</>');
            $this->outputWriter->writeln('<fg=gray>Target: ' . ($this->target ?? 'current directory') . '</>');
            $this->outputWriter->writeln('<fg=cyan>' . str_repeat('─', max(48, min(120, new Terminal()->getWidth()))) . '</>');
            $this->renderedLines = 1;

            return;
        }

        $this->render();
    }

    public function step(string $stage): void
    {
        $this->stage = $stage;
        $this->append($stage);
        if (!$this->supportsRedraw()) {
            $this->outputWriter->writeln('<fg=yellow>↳ ' . $stage . '</>');

            return;
        }

        $this->render();
    }

    public function processOutput(string $type, string $buffer): void
    {
        $buffer = preg_replace('/\e\[[;\d]*[A-Za-z]/', '', $buffer) ?? $buffer;
        foreach (preg_split('/\r\n|\r|\n/', $buffer) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                if ($line === $this->stage) {
                    continue;
                }

                $this->append($line);
                if (!$this->supportsRedraw()) {
                    $this->outputWriter->writeln('<fg=gray>' . $line . '</>');
                }
            }
        }
        if ($this->supportsRedraw()) {
            $this->render();
        }
    }

    public function finish(string $stage = 'Generation complete'): void
    {
        $this->stage = $stage;
        if (!$this->supportsRedraw()) {
            return;
        }

        $this->settleOutput();
    }

    private function append(string $line): void
    {
        $this->output[] = $line;
        $this->output = array_slice($this->output, -$this->maxOutputLines);
    }

    private function render(): void
    {
        $width = max(48, min(120, new Terminal()->getWidth()));
        $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'][$this->animationFrame++ % 10];
        $lines = [
            '<fg=cyan;options=bold>' . $spinner . ' Creating Mage-OS project</>',
            '<fg=yellow>↳ ' . $this->fit($this->stage, max(0, $width - 2)) . '</>',
            '<fg=gray>Target: ' . $this->fit($this->target ?? 'current directory', max(0, $width - 8)) . '</>',
            '<fg=cyan>' . str_repeat('─', $width) . '</>',
        ];
        foreach ($this->output as $line) {
            $lines[] = $this->formatOutputLine($line, $width);
        }
        for ($index = count($this->output); $index < $this->maxOutputLines; $index++) {
            $lines[] = '';
        }

        $decorated = $this->supportsRedraw();
        if ($this->renderedLines > 0 && $decorated) {
            $this->outputWriter->write("\033[{$this->renderedLines}A");
        }
        $linePrefix = $decorated ? "\033[2K\r" : '';
        foreach ($lines as $line) {
            $this->outputWriter->writeln($linePrefix . $line);
        }
        $this->renderedLines = count($lines);
    }

    private function settleOutput(): void
    {
        if ($this->renderedLines === 0 || !$this->supportsRedraw()) {
            return;
        }

        $width = max(48, min(120, new Terminal()->getWidth()));
        $this->outputWriter->write("\033[{$this->renderedLines}A");
        foreach ($this->output as $line) {
            $this->outputWriter->writeln("\033[2K\r" . $this->formatOutputLine($line, $width));
        }
        $this->outputWriter->write("\033[0J");
        $this->renderedLines = count($this->output);
    }

    private function supportsRedraw(): bool
    {
        if ($this->outputWriter->isDecorated()) {
            return true;
        }

        if (!function_exists('stream_isatty')) {
            return false;
        }

        return (defined('STDOUT') && stream_isatty(STDOUT))
            || (defined('STDIN') && stream_isatty(STDIN));
    }

    private function formatOutputLine(string $line, int $width): string
    {
        return '<fg=gray>' . $this->fit($line, $width) . '</>';
    }

    private function fit(string $text, int $width): string
    {
        $visibleText = preg_replace('/<[^>]+>|\e\[[;\d]*m/', '', $text) ?? $text;
        $length = mb_strwidth($visibleText);

        return $length <= $width
            ? $text . str_repeat(' ', $width - $length)
            : mb_substr($visibleText, 0, max(0, $width - 1)) . '…';
    }
}
