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

final readonly class InstallationResult
{
    public function __construct(
        public string $status,
        public array $steps = [],
        public array $errors = [],
    ) {}

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['planned', 'generated', 'installed', 'deferred'], true);
    }
}
