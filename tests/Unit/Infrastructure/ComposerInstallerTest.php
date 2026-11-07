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

use Hephaestus\Infrastructure\Composer\ComposerInstaller;
use Hephaestus\Infrastructure\Process\ProcessRunner;

function withFakeComposer(callable $callback): void
{
    $directory = sys_get_temp_dir() . '/mageos-composer-' . bin2hex(random_bytes(4));
    mkdir($directory);
    $binary = $directory . '/composer';
    file_put_contents($binary, "#!/bin/sh\nprintf 'fake composer output'\n");
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

it('runs Composer install and create-project with the expected commands', function (): void {
    withFakeComposer(function (): void {
        $installer = new ComposerInstaller(new ProcessRunner());
        $chunks = [];
        $install = $installer->install(sys_get_temp_dir(), static function (string $type, string $buffer) use (&$chunks): void {
            $chunks[] = [$type, $buffer];
        });
        $latest = $installer->createProject('mage-os/project-community-edition', 'latest', 'https://repo.test/', '/tmp/project');
        $versioned = $installer->createProject('mage-os/project-community-edition', '4.0.0', 'https://repo.test/', '/tmp/project');

        expect($install['exit_code'])->toBe(0)
            ->and($latest['exit_code'])->toBe(0)
            ->and($versioned['exit_code'])->toBe(0)
            ->and($install['output'])->toBe('fake composer output')
            ->and($chunks)->not->toBeEmpty();
    });
});
