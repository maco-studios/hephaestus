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

function testCatalog(): \MageOsInstaller\Domain\Catalog\Catalog
{
    return (new DefinitionLoader(__DIR__ . '/../../resources/definitions'))->load();
}

it('loads the MageOS Maker definition categories', function (): void {
    $catalog = testCatalog();

    expect($catalog->profiles)->not->toBeEmpty()
        ->and($catalog->sets)->not->toBeEmpty()
        ->and($catalog->layers)->not->toBeEmpty()
        ->and($catalog->profileGroups)->not->toBeEmpty()
        ->and($catalog->addons)->not->toBeEmpty();
});

it('creates a default selection from the catalog', function (): void {
    $catalog = testCatalog();
    $selection = (new SelectionResolver($catalog))->defaults('latest');

    expect($selection->release)->toBe('latest')
        ->and($selection->profile)->toBe($catalog->defaultProfile())
        ->and($selection->mode)->toBeIn(['subtractive', 'additive']);
});

it('plans a project with a base requirement and disabled set replacements', function (): void {
    $catalog = testCatalog();
    $selection = new ProjectSelection('latest', disabledSets: ['wishlist']);
    $plan = (new ProjectPlanner($catalog))->plan($selection);

    expect($plan->requires)->toHaveKey('mage-os/module-contact')
        ->and($plan->requires['mage-os/magento2-base'])->toBe('4.0.0')
        ->and($plan->requires['mage-os/magento-composer-installer'])->toBe('4.0.0')
        ->and($plan->replaces)->toHaveKey('mage-os/module-wishlist');
});

it('uses the requested project name in the generated Composer manifest', function (): void {
    $catalog = testCatalog();
    $selection = new ProjectSelection('latest', projectName: 'acme/storefront');
    $plan = (new ProjectPlanner($catalog))->plan($selection);

    expect($plan->baseProject['name'])->toBe('acme/storefront');
});

it('rejects unknown sets before generation', function (): void {
    $catalog = testCatalog();

    (new ProjectPlanner($catalog))->plan(new ProjectSelection('latest', disabledSets: ['does-not-exist']));
})->throws(InvalidArgumentException::class);
