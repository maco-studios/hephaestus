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

namespace MageOsInstaller\Domain\Installation;

use InvalidArgumentException;

final class InstallationStateMachine
{
    private const array TRANSITIONS = [
        'planned' => ['generated', 'cancelled', 'failed'],
        'generated' => ['installed', 'deferred', 'cancelled', 'failed'],
        'installed' => [],
        'deferred' => ['installed', 'failed'],
        'cancelled' => [],
        'failed' => ['generated', 'deferred', 'installed'],
    ];

    public function transition(string $from, string $to): string
    {
        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidArgumentException("Invalid installation transition: {$from} → {$to}");
        }

        return $to;
    }
}
