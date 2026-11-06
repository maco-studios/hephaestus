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

namespace MageOsInstaller\Domain\Planning;

use MageOsInstaller\Domain\Selection\ProjectSelection;

final readonly class ProjectPlan
{
    public function __construct(
        public ProjectSelection $selection,
        public array $baseProject,
        public array $requires,
        public array $replaces,
        public array $repositories = [],
        public array $credentials = [],
        public array $warnings = [],
        public array $followUpActions = [],
        public string $catalogSource = 'bundled',
    ) {}

    public function toArray(): array
    {
        return [
            'selection' => $this->selection->toArray(),
            'base_project' => $this->baseProject,
            'requires' => $this->requires,
            'replaces' => $this->replaces,
            'repositories' => $this->repositories,
            'credentials' => $this->credentials,
            'warnings' => $this->warnings,
            'follow_up_actions' => $this->followUpActions,
            'catalog_source' => $this->catalogSource,
        ];
    }
}
