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

namespace Hephaestus\Infrastructure\Process;

use Symfony\Component\Process\Process;

final class ProcessRunner
{
    public function run(array $command, string $cwd, ?callable $onOutput = null): array
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(null);
        $process->run($onOutput);

        return [
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
    }
}
