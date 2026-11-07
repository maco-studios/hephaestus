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

use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;

it('renders the same plan for the same selection', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $planner = new ProjectPlanner($catalog);
    $selection = new ProjectSelection('latest', disabledSets: ['wishlist']);

    expect($planner->plan($selection)->toArray())->toBe($planner->plan($selection)->toArray());
});
