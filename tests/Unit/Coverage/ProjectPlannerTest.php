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

use MageOsInstaller\Domain\Catalog\Catalog;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use InvalidArgumentException;

function projectPlannerCoverageCatalog(array $overrides = []): Catalog
{
    return new Catalog(
        profiles: [],
        sets: $overrides['sets'] ?? [
            'enabled-set' => ['packages' => ['mage-os/module-enabled']],
        ],
        layers: $overrides['layers'] ?? [
            'stock-layer' => ['stock' => true, 'packages' => ['mage-os/module-stock']],
        ],
        profileGroups: [],
        addons: $overrides['addons'] ?? [
            'enabled-addon' => [
                'packages' => ['mage-os/module-addon'],
                'repositories' => [['type' => 'vcs', 'url' => 'https://example.test/addon']],
                'notes' => ['Enable the add-on configuration.'],
            ],
        ],
        releases: [],
        composerTemplates: $overrides['composerTemplates'] ?? [
            'latest' => [
                'name' => 'vendor/template',
                'version' => '1.0.0',
                'require' => [
                    'mage-os/project-community-edition' => '^4.0',
                    'vendor/template-dependency' => '^1.0',
                ],
            ],
        ],
        sourceVersion: 'coverage-fixture',
    );
}

it('reports every unknown add-on in the public planning error', function (): void {
    $planner = new ProjectPlanner(projectPlannerCoverageCatalog());

    expect(fn (): mixed => $planner->plan(new ProjectSelection(
        'latest',
        disabledSets: ['missing-set'],
        enabledAddons: ['missing-addon'],
    )))->toThrow(InvalidArgumentException::class, 'Unknown set: missing-set. Unknown add-on: missing-addon.');
});

it('uses the latest template fallback when the latest release template is incomplete', function (): void {
    $planner = new ProjectPlanner(projectPlannerCoverageCatalog([
        'composerTemplates' => [
            'latest' => [
                'description' => 'Fallback template',
                'type' => 'library',
                'require' => 'not-an-array',
            ],
        ],
    ]));

    $plan = $planner->plan(new ProjectSelection('latest'));

    expect($plan->baseProject)
        ->toMatchArray([
            'name' => 'mage-os/project-community-edition',
            'description' => 'Fallback template',
            'type' => 'library',
        ])
        ->and($plan->requires)
        ->toMatchArray([
            'mage-os/magento-composer-installer' => '4.0.0',
            'mage-os/magento2-base' => '4.0.0',
        ]);
});

it('rejects an unknown enabled set before attempting to add its packages', function (): void {
    $planner = new ProjectPlanner(projectPlannerCoverageCatalog());

    expect(fn (): mixed => $planner->plan(new ProjectSelection(
        'latest',
        mode: 'additive',
        enabledSets: ['missing-set'],
    )))->toThrow(InvalidArgumentException::class, 'Unknown set: missing-set.');
});

it('rejects an unknown enabled layer before attempting to add its packages', function (): void {
    $planner = new ProjectPlanner(projectPlannerCoverageCatalog());

    expect(fn (): mixed => $planner->plan(new ProjectSelection(
        'latest',
        mode: 'additive',
        enabledLayers: ['missing-layer'],
    )))->toThrow(InvalidArgumentException::class, 'Unknown layer: missing-layer.');
});

it('skips non-installable and non-replaceable packages from disabled layers', function (): void {
    $planner = new ProjectPlanner(projectPlannerCoverageCatalog([
        'layers' => [
            'disabled-layer' => [
                'stock' => false,
                'packages' => [
                    ['name' => 'mage-os/not-installable', 'installable' => false],
                    ['name' => 'mage-os/not-replaceable', 'replace' => false],
                    ['name' => 'mage-os/replaced-layer-package'],
                ],
            ],
        ],
    ]));

    $plan = $planner->plan(new ProjectSelection('latest', disabledLayers: ['disabled-layer']));

    expect($plan->replaces)
        ->not->toHaveKeys(['mage-os/not-installable', 'mage-os/not-replaceable'])
        ->toHaveKey('mage-os/replaced-layer-package', '*');
});
