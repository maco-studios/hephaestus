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

namespace Hephaestus\Infrastructure\Composer;

use Hephaestus\Infrastructure\Process\ProcessRunner;

final readonly class ComposerInstaller
{
    public function __construct(private ProcessRunner $runner) {}

    public function install(string $cwd, ?callable $onOutput = null): array
    {
        return $this->runner->run(['composer', 'install', '--no-interaction', '--prefer-dist'], $cwd, $onOutput);
    }

    public function createProject(string $package, ?string $version, string $repository, string $target, ?callable $onOutput = null): array
    {
        $packageReference = !in_array($version, [null, '', 'latest'], true)
            ? $package . ':' . $version
            : $package;

        return $this->runner->run([
            'composer',
            'create-project',
            '--no-install',
            '--no-interaction',
            '--no-scripts',
            '--prefer-dist',
            '--repository-url=' . $repository,
            $packageReference,
            $target,
        ], getcwd() ?: '.', $onOutput);
    }
}
