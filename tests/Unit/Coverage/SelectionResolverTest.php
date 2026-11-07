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

function selectionResolverCoverageResolver(): SelectionResolver
{
    return new SelectionResolver(
        new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load(),
    );
}

it('reports an unsupported profile and profile-group option', function (): void {
    $selection = new ProjectSelection(
        release: 'latest',
        profile: 'missing-profile',
        profileGroups: ['theme' => 'missing-theme'],
    );

    expect(selectionResolverCoverageResolver()->validate($selection))->toBe([
        'Unknown profile: missing-profile.',
        'Unknown option missing-theme for profile group theme.',
    ]);
});

it('falls back when a profile-group option requires a different group choice', function (): void {
    $selection = new ProjectSelection(
        release: 'latest',
        profileGroups: [
            'theme' => 'luma',
            'checkout' => 'hyva-checkout',
        ],
    );

    $resolved = selectionResolverCoverageResolver()->resolve($selection);

    expect($resolved->profileGroups['checkout'])->toBe('default')
        ->and($resolved->enabledAddons)->not->toContain('hyva-checkout');
});
