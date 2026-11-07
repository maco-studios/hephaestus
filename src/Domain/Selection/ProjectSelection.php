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

namespace Hephaestus\Domain\Selection;

use InvalidArgumentException;

final readonly class ProjectSelection
{
    public function __construct(
        public string $release,
        public string $distribution = 'standard',
        public ?string $profile = null,
        public string $mode = 'subtractive',
        public array $profileGroups = [],
        public array $disabledSets = [],
        public array $enabledSets = [],
        public array $disabledLayers = [],
        public array $enabledLayers = [],
        public array $enabledAddons = [],
        public array $disabledSubtoggles = [],
        public array $enabledOptionSubtoggles = [],
        public array $optionVariants = [],
        public array $forcedSets = [],
        public array $forcedLayers = [],
        public array $forcedAddons = [],
        public ?string $projectName = null,
        public bool $install = true,
    ) {
        if (!in_array($this->mode, ['subtractive', 'additive'], true)) {
            throw new InvalidArgumentException('Mode must be subtractive or additive.');
        }
        if ($this->projectName !== null) {
            new ProjectName($this->projectName);
        }
    }

    public function isAdditive(): bool
    {
        return $this->mode === 'additive';
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
