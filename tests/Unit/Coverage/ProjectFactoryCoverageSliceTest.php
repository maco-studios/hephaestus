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

use Hephaestus\Application\ProjectFactory;
use Hephaestus\Domain\Planning\ProjectPlanner;
use Hephaestus\Domain\Selection\ProjectSelection;
use Hephaestus\Domain\Selection\SelectionResolver;
use Hephaestus\Infrastructure\Catalog\DefinitionLoader;
use Hephaestus\Infrastructure\Composer\ComposerInstaller;
use Hephaestus\Infrastructure\Composer\ComposerJsonRenderer;
use Hephaestus\Infrastructure\Filesystem\ProjectWriter;
use Hephaestus\Infrastructure\Filesystem\TargetDirectory;
use Hephaestus\Infrastructure\Process\ProcessRunner;

function projectFactoryCoverageSliceFactory(): ProjectFactory
{
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();

    return new ProjectFactory(
        new SelectionResolver($catalog),
        new ProjectPlanner($catalog),
        new ProjectWriter(new ComposerJsonRenderer()),
        new ComposerInstaller(new ProcessRunner()),
    );
}

function projectFactoryCoverageSliceWithComposer(callable $callback, bool $failInstall = false): void
{
    $directory = sys_get_temp_dir() . '/mageos-project-factory-coverage-' . bin2hex(random_bytes(4));
    mkdir($directory);
    $binary = $directory . '/composer';
    $log = $directory . '/commands.log';
    $failure = $failInstall ? '1' : '0';
    file_put_contents($binary, "#!/bin/sh\nprintf '%s\\n' \"$*\" >> '{$log}'\nif [ \"$1\" = \"install\" ] && [ '{$failure}' = \"1\" ]; then\n  printf 'dependency installation failed' >&2\n  exit 1\nfi\nexit 0\n");
    chmod($binary, 0755);
    $previousPath = getenv('PATH') ?: '';
    putenv('PATH=' . $directory . ':' . $previousPath);

    try {
        $callback($log);
    } finally {
        putenv('PATH=' . $previousPath);
        unlink($binary);
        if (is_file($log)) {
            unlink($log);
        }
        rmdir($directory);
    }
}

it('uses the minimal base project and reports the installation step for additive selections', function (): void {
    $targetPath = sys_get_temp_dir() . '/mageos-additive-coverage-' . bin2hex(random_bytes(4));
    $steps = [];

    projectFactoryCoverageSliceWithComposer(function (string $log) use ($targetPath, &$steps): void {
        $result = projectFactoryCoverageSliceFactory()->create(
            new TargetDirectory($targetPath),
            new ProjectSelection('latest', mode: 'additive'),
            dryRun: false,
            install: true,
            onStep: static function (string $step) use (&$steps): void {
                $steps[] = $step;
            },
        );

        expect($result->status)->toBe('installed')
            ->and($steps)->toContain('Installing Composer dependencies')
            ->and(file_get_contents($log))->toContain('create-project')
            ->and(file_get_contents($log))->toContain('mage-os/project-minimal-edition');
    });

    unlink($targetPath . '/composer.json');
    rmdir($targetPath);
});

it('reports dependency installation failures after generation', function (): void {
    $targetPath = sys_get_temp_dir() . '/mageos-install-failure-coverage-' . bin2hex(random_bytes(4));

    projectFactoryCoverageSliceWithComposer(function () use ($targetPath): void {
        $result = projectFactoryCoverageSliceFactory()->create(
            new TargetDirectory($targetPath),
            new ProjectSelection('latest'),
            dryRun: false,
            install: true,
        );

        expect($result->status)->toBe('failed')
            ->and($result->steps)->toBe(['generated' => $targetPath])
            ->and($result->errors)->toBe(['dependency installation failed']);
    }, failInstall: true);

    unlink($targetPath . '/composer.json');
    rmdir($targetPath);
});
