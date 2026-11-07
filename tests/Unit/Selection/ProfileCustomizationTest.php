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

use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;

it('allows profile defaults to be customized', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $selection = new SelectionResolver($catalog)->defaults('latest');
    $custom = new SelectionResolver($catalog)->merge($selection, ['disabledSets' => ['wishlist']]);

    expect($custom->profile)->toBe($selection->profile)
        ->and($custom->disabledSets)->toContain('wishlist');
});

it('applies a selected lite profile instead of leaving the full profile active', function (): void {
    $catalog = testCatalog();
    $resolver = new SelectionResolver($catalog);
    $selection = new \MageOsInstaller\Domain\Selection\ProjectSelection('latest', profile: 'mageos-lite');
    $resolved = $resolver->resolve($selection);

    expect($resolved->disabledSets)->toContain('wishlist')
        ->and($resolved->profile)->toBe('mageos-lite');
});
