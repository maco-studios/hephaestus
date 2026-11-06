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

namespace MageOsInstaller\Infrastructure\Composer;

use Composer\Script\Event;
use RuntimeException;
use Throwable;

final class BootstrapScript
{
    public static function postCreateProject(Event $event): void
    {
        $workingDirectory = getcwd() ?: dirname(__DIR__, 3);
        $binary = $workingDirectory . '/bin/mageos';
        if (!is_file($binary) || getenv('MAGEOS_INSTALLER_SKIP') === '1') {
            return;
        }

        $stagingDirectory = dirname($workingDirectory) . '/.mageos-project-' . bin2hex(random_bytes(8));
        if (!mkdir($stagingDirectory, 0775, true) && !is_dir($stagingDirectory)) {
            throw new RuntimeException("Unable to create staging directory: {$stagingDirectory}");
        }

        try {
            $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($binary) . ' new ' . escapeshellarg($stagingDirectory);
            passthru($command, $exitCode);
            if ($exitCode !== 0) {
                throw new RuntimeException("Mage-OS project generation failed with exit code {$exitCode}");
            }

            self::replaceContents($workingDirectory, $stagingDirectory);
        } catch (Throwable $exception) {
            self::removePath($stagingDirectory);
            throw $exception;
        }
    }

    private static function replaceContents(string $target, string $source): void
    {
        foreach (new \FilesystemIterator($target) as $entry) {
            self::removePath($entry->getPathname());
        }

        foreach (new \FilesystemIterator($source) as $entry) {
            $destination = $target . '/' . $entry->getBasename();
            if (!rename($entry->getPathname(), $destination)) {
                throw new RuntimeException("Unable to move generated project item: {$entry->getBasename()}");
            }
        }

        if (!rmdir($source)) {
            throw new RuntimeException("Unable to remove staging directory: {$source}");
        }
    }

    private static function removePath(string $path): void
    {
        if (is_dir($path) && !is_link($path)) {
            foreach (new \FilesystemIterator($path) as $entry) {
                self::removePath($entry->getPathname());
            }

            rmdir($path);
            return;
        }

        if (is_file($path) || is_link($path)) {
            unlink($path);
        }
    }
}
