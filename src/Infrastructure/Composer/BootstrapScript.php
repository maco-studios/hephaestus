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

final class BootstrapScript
{
    public static function postCreateProject(Event $event): void
    {
        $workingDirectory = getcwd() ?: dirname(__DIR__, 3);
        $binary = $workingDirectory . '/bin/mageos';
        if (!is_file($binary) || getenv('MAGEOS_INSTALLER_SKIP') === '1') {
            return;
        }

        passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($binary) . ' new .');
    }
}
