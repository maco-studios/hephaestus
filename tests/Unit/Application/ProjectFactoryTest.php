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

use MageOsInstaller\Application\ProjectFactory;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;
use MageOsInstaller\Infrastructure\Composer\ComposerInstaller;
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;
use MageOsInstaller\Infrastructure\Filesystem\ProjectWriter;
use MageOsInstaller\Infrastructure\Filesystem\TargetDirectory;
use MageOsInstaller\Infrastructure\Process\ProcessRunner;

function withFactoryFakeComposer(callable $callback, int $exitCode = 0): void
{
    $directory = sys_get_temp_dir() . '/mageos-factory-composer-' . bin2hex(random_bytes(4));
    mkdir($directory);
    $binary = $directory . '/composer';
    file_put_contents($binary, "#!/bin/sh\nprintf 'fake composer output'\nexit {$exitCode}\n");
    chmod($binary, 0755);
    $previousPath = getenv('PATH');
    putenv('PATH=' . $directory . ':' . $previousPath);

    try {
        $callback();
    } finally {
        putenv('PATH=' . $previousPath);
        unlink($binary);
        rmdir($directory);
    }
}

function projectFactory(): ProjectFactory
{
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $resolver = new SelectionResolver($catalog);

    return new ProjectFactory(
        $resolver,
        new ProjectPlanner($catalog),
        new ProjectWriter(new ComposerJsonRenderer()),
        new ComposerInstaller(new ProcessRunner()),
    );
}

it('returns a planned result without touching the target', function (): void {
    $steps = [];
    $targetPath = sys_get_temp_dir() . '/mageos-plan-' . bin2hex(random_bytes(4));
    $result = projectFactory()->create(
        new TargetDirectory($targetPath),
        new ProjectSelection('latest'),
        dryRun: true,
        install: false,
        onStep: static function (string $step) use (&$steps): void {
            $steps[] = $step;
        },
    );

    expect($result->status)->toBe('planned')
        ->and($result->steps)->toHaveKey('plan')
        ->and($steps)->toBe(['Resolving the selected composition'])
        ->and(file_exists($targetPath))->toBeFalse();
});

it('rejects invalid selections before generation', function (): void {
    projectFactory()->plan(new ProjectSelection('not-a-release'));
})->throws(InvalidArgumentException::class);

it('generates a project and can defer dependency installation', function (): void {
    $path = sys_get_temp_dir() . '/mageos-generated-' . bin2hex(random_bytes(4));
    mkdir($path);
    $steps = [];

    withFactoryFakeComposer(function () use ($path, &$steps): void {
        $result = projectFactory()->create(
            new TargetDirectory($path),
            new ProjectSelection('latest'),
            dryRun: false,
            install: false,
            onStep: static function (string $step) use (&$steps): void {
                $steps[] = $step;
            },
        );

        expect($result->status)->toBe('deferred')
            ->and(file_exists($path . '/composer.json'))->toBeTrue()
            ->and($steps)->toBe([
                'Resolving the selected composition',
                'Downloading the Mage-OS project base',
                'Writing the generated Composer configuration',
            ]);
    });

    unlink($path . '/composer.json');
    rmdir($path);
});

it('reports Composer bootstrap failures', function (): void {
    $bootstrapPath = sys_get_temp_dir() . '/mageos-bootstrap-failure-' . bin2hex(random_bytes(4));
    mkdir($bootstrapPath);
    withFactoryFakeComposer(function () use ($bootstrapPath): void {
        $result = projectFactory()->create(new TargetDirectory($bootstrapPath), new ProjectSelection('latest'), false, false);

        expect($result->status)->toBe('failed')
            ->and($result->errors)->toContain('fake composer output');
    }, 1);
    rmdir($bootstrapPath);
});

it('installs dependencies after writing the generated Composer configuration', function (): void {
    $path = sys_get_temp_dir() . '/mageos-installed-' . bin2hex(random_bytes(4));
    mkdir($path);
    withFactoryFakeComposer(function () use ($path): void {
        $result = projectFactory()->create(new TargetDirectory($path), new ProjectSelection('latest'), false, true);

        expect($result->status)->toBe('installed')
            ->and($result->steps)->toHaveKey('composer', 'installed');
    });
    unlink($path . '/composer.json');
    rmdir($path);
});
