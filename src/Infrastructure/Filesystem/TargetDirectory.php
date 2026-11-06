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

namespace MageOsInstaller\Infrastructure\Filesystem;

use RuntimeException;

final class TargetDirectory
{
    public function __construct(public readonly string $path) {}

    public function assertSafe(bool $force = false): void
    {
        if (!file_exists($this->path)) {
            return;
        }
        if (!is_dir($this->path)) {
            throw new RuntimeException("Target is not a directory: {$this->path}");
        }
        $entries = array_diff(scandir($this->path) ?: [], ['.', '..']);
        if ($entries !== [] && !$force) {
            throw new RuntimeException("Target directory is not empty: {$this->path}");
        }
    }

    public function ensureExists(): void
    {
        if (!is_dir($this->path) && !mkdir($this->path, 0775, true) && !is_dir($this->path)) {
            throw new RuntimeException("Unable to create target directory: {$this->path}");
        }
    }
}
