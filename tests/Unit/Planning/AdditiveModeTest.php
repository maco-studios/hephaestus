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

it('does not select stock sets by default in additive mode', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $plan = (new ProjectPlanner($catalog))->plan(new ProjectSelection('latest', mode: 'additive'));

    expect($plan->requires)->not->toHaveKey('mage-os/product-minimal-edition')
        ->and($plan->requires)->not->toHaveKey('mage-os/module-wishlist');
});

it('adds only explicitly selected sets in additive mode', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $plan = (new ProjectPlanner($catalog))->plan(new ProjectSelection('latest', mode: 'additive', enabledSets: ['wishlist']));

    expect($plan->requires)->toHaveKey('mage-os/module-wishlist');
});
