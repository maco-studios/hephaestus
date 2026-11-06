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

namespace MageOsInstaller\Domain\Catalog;

final readonly class Catalog
{
    public function __construct(
        public array $profiles,
        public array $sets,
        public array $layers,
        public array $profileGroups,
        public array $addons,
        public array $releases,
        public array $composerTemplates = [],
        public string $sourceVersion = 'bundled',
    ) {}

    public function defaultProfile(): string
    {
        foreach ($this->profiles as $name => $profile) {
            if (($profile['default'] ?? false) === true) {
                return $name;
            }
        }

        return array_key_first($this->profiles) ?? 'mageos-full';
    }

    public function defaultGroupOption(string $group): ?string
    {
        foreach ($this->profileGroups[$group]['options'] ?? [] as $option) {
            if (($option['default'] ?? false) === true) {
                return $option['name'] ?? null;
            }
        }

        return isset($this->profileGroups[$group]['options'][0]['name'])
            ? $this->profileGroups[$group]['options'][0]['name']
            : null;
    }

    public function availableReleases(): array
    {
        return $this->releases !== [] ? array_keys($this->releases) : ['latest'];
    }
}
