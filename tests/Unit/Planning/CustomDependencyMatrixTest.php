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
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;

function matrixCatalog(): \MageOsInstaller\Domain\Catalog\Catalog
{
    return new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
}

it('generates module-scoped requirements for a full storefront selection', function (): void {
    $catalog = matrixCatalog();
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection(
        'latest',
        profile: 'mageos-full',
    ));

    expect($plan->requires)
        ->toHaveKey('mage-os/theme-frontend-luma')
        ->toHaveKey('mage-os/module-contact')
        ->toHaveKey('mage-os/module-graph-ql')
        ->not->toHaveKey('mage-os/product-community-edition');
});

it('changes the module scope when a storefront capability is disabled', function (): void {
    $catalog = matrixCatalog();
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection(
        'latest',
        profile: 'mageos-full',
        disabledSets: ['luma', 'contact'],
    ));

    expect($plan->requires)
        ->not->toHaveKey('mage-os/theme-frontend-luma')
        ->not->toHaveKey('mage-os/module-contact')
        ->toHaveKey('mage-os/module-graph-ql');
});

it('generates only selected module families for an additive tools selection', function (): void {
    $catalog = matrixCatalog();
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection(
        'latest',
        mode: 'additive',
        enabledSets: ['catalog-widget'],
        enabledLayers: ['graphql'],
    ));

    expect($plan->requires)
        ->toHaveKey('mage-os/module-catalog-widget')
        ->toHaveKey('mage-os/module-graph-ql')
        ->not->toHaveKey('mage-os/module-wishlist')
        ->not->toHaveKey('mage-os/product-minimal-edition');
});

it('keeps the named project profiles materially different in the dependency matrix', function (): void {
    $catalog = matrixCatalog();
    $resolver = new SelectionResolver($catalog);
    $planner = new ProjectPlanner($catalog);

    $requirements = [];
    foreach (['mageos-full', 'mageos-lite', 'mageos-headless', 'mageos-minimal'] as $profile) {
        $requirements[$profile] = $planner->plan(
            $resolver->resolve(new ProjectSelection('latest', profile: $profile)),
        )->requires;
    }

    expect($requirements['mageos-full'])
        ->toHaveKey('mage-os/theme-frontend-luma')
        ->toHaveKey('mage-os/module-wishlist');
    expect($requirements['mageos-lite'])
        ->toHaveKey('mage-os/theme-frontend-luma')
        ->not->toHaveKey('mage-os/module-wishlist')
        ->not->toHaveKey('mage-os/module-graph-ql');
    expect($requirements['mageos-headless'])
        ->not->toHaveKey('mage-os/theme-frontend-luma')
        ->toHaveKey('mage-os/module-graph-ql');
    expect($requirements['mageos-minimal'])
        ->not->toHaveKey('mage-os/theme-frontend-luma')
        ->not->toHaveKey('mage-os/module-wishlist');
});

it('preserves catalog-only packages without emitting un-installable Composer roots', function (): void {
    $catalog = matrixCatalog();
    $plan = new ProjectPlanner($catalog)->plan(new ProjectSelection('latest', profile: 'mageos-full'));

    expect($catalog->sets['adobe-stock']['packages'])
        ->toContain(['name' => 'mage-os/adobe-stock-integration', 'installable' => false]);
    expect($plan->requires)
        ->not->toHaveKey('mage-os/adobe-stock-integration')
        ->not->toHaveKey('mage-os/module-review-reports');
});
