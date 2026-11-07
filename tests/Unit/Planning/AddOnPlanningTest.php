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

use Hephaestus\Domain\Planning\ProjectPlanner;
use Hephaestus\Domain\Selection\ProjectSelection;
use Hephaestus\Infrastructure\Catalog\DefinitionLoader;

it('adds optional packages and notes to the project plan', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $addon = array_key_first($catalog->addons);
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection('latest', enabledAddons: [$addon]));

    expect($plan->requires)->not->toBeEmpty();
});
