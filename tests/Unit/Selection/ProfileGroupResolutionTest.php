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

use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;

it('keeps profile group choices keyed by group name', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $selection = (new SelectionResolver($catalog))->resolve(new ProjectSelection('latest', profileGroups: ['theme' => 'luma']));

    expect($selection->profileGroups)->toHaveKey('theme');
});

it('falls back from an unknown profile group option', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $resolver = new SelectionResolver($catalog);
    $selection = $resolver->resolve(new ProjectSelection('latest', profileGroups: ['theme' => 'missing']));

    expect($selection->profileGroups['theme'])->toBe($catalog->defaultGroupOption('theme'));
});
