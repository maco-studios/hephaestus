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

it('adds optional packages and notes to the project plan', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $addon = array_key_first($catalog->addons);
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection('latest', enabledAddons: [$addon]));

    expect($plan->requires)->not->toBeEmpty();
});
