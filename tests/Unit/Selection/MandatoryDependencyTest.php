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

use Hephaestus\Domain\Selection\ProjectSelection;
use Hephaestus\Domain\Selection\SelectionResolver;
use Hephaestus\Infrastructure\Catalog\DefinitionLoader;

it('forces a required layer back on when an active set depends on it', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $selection = new ProjectSelection('latest', disabledLayers: ['webapi']);
    $resolved = new SelectionResolver($catalog)->resolve($selection);

    expect($resolved->forcedLayers)->toContain('webapi')
        ->and($resolved->disabledLayers)->not->toContain('webapi')
        ->and($resolved->enabledLayers)->toContain('webapi');
});

it('forces dependencies from profile-group choices', function (): void {
    $catalog = new DefinitionLoader(__DIR__ . '/../../../resources/definitions')->load();
    $selection = new ProjectSelection('latest', profileGroups: ['theme' => 'hyva']);
    $resolved = new SelectionResolver($catalog)->resolve($selection);

    expect($resolved->forcedLayers)->toContain('graphql');
});
