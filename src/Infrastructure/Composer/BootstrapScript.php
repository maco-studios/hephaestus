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
use MageOsInstaller\Domain\Selection\ProjectName;
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
            $previousDisplayTarget = getenv('MAGEOS_DISPLAY_TARGET');
            putenv('MAGEOS_DISPLAY_TARGET=' . $workingDirectory);
            $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($binary) . ' new ' . escapeshellarg($stagingDirectory);
            try {
                passthru($command, $exitCode);
            } finally {
                if ($previousDisplayTarget === false) {
                    putenv('MAGEOS_DISPLAY_TARGET');
                } else {
                    putenv('MAGEOS_DISPLAY_TARGET=' . $previousDisplayTarget);
                }
            }
            if ($exitCode !== 0) {
                throw new RuntimeException("Mage-OS project generation failed with exit code {$exitCode}");
            }

            if (self::isImplicitBootstrapDirectory($event, $workingDirectory)) {
                self::moveToProjectDirectory($workingDirectory, $stagingDirectory);
            } else {
                self::replaceContents($workingDirectory, $stagingDirectory);
            }
        } catch (Throwable $exception) {
            self::removePath($stagingDirectory);
            throw $exception;
        }
    }

    private static function isImplicitBootstrapDirectory(Event $event, string $workingDirectory): bool
    {
        $packageName = $event->getComposer()->getPackage()->getPrettyName();
        $packageDirectory = substr($packageName, strrpos($packageName, '/') + 1);

        return basename($workingDirectory) === $packageDirectory;
    }

    private static function moveToProjectDirectory(string $workingDirectory, string $source): void
    {
        $manifestPath = $source . '/composer.json';
        if (!is_file($manifestPath)) {
            throw new RuntimeException('Generated project is missing composer.json.');
        }

        try {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            $projectName = new ProjectName((string) ($manifest['name'] ?? ''));
        } catch (Throwable $exception) {
            throw new RuntimeException('Generated project has an invalid Composer name.', 0, $exception);
        }

        $destination = dirname($workingDirectory) . '/' . $projectName->directoryName();
        if ($destination === $workingDirectory) {
            self::replaceContents($workingDirectory, $source);

            return;
        }
        if (file_exists($destination)) {
            throw new RuntimeException("Unable to create generated project directory: {$destination} already exists.");
        }
        if (!rename($source, $destination)) {
            throw new RuntimeException("Unable to move generated project to {$destination}.");
        }

        self::removePath($workingDirectory);
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
