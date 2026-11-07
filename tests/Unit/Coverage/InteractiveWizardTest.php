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

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use MageOsInstaller\Application\Wizard\InteractiveWizard;
use MageOsInstaller\Domain\Catalog\Catalog;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;

function interactiveWizardCoverageCatalog(bool $withAddons = true, bool $withProfileGroups = true): Catalog
{
    return new Catalog(
        profiles: [
            'full' => [
                'default' => true,
                'label' => 'Full profile',
                'description' => 'The complete starter profile.',
                'selection' => [],
            ],
        ],
        sets: [
            'search' => ['label' => 'Search', 'description' => 'Search support.'],
            'catalog' => ['label' => 'Catalog', 'description' => 'Catalog support.'],
        ],
        layers: [],
        profileGroups: $withProfileGroups ? [
            'frontend' => [
                'label' => 'Frontend',
                'options' => [
                    [
                        'name' => 'luma',
                        'label' => 'Luma',
                        'description' => 'The Luma storefront.',
                        'default' => true,
                    ],
                    [
                        'name' => 'hyva',
                        'label' => 'Hyva',
                        'description' => 'The Hyva storefront.',
                    ],
                ],
            ],
            'empty' => [],
        ] : [],
        addons: $withAddons
            ? ['redis' => ['label' => 'Redis', 'description' => 'Redis cache support.']]
            : [],
        releases: ['1.0' => [], '2.0' => []],
    );
}

it('collects a subtractive selection and invokes the project-name callback', function (): void {
    Prompt::fake([
        ...str_split('vendor/demo'),
        Key::ENTER,
        Key::ENTER,
        Key::ENTER,
        Key::ENTER,
        Key::ENTER,
        Key::ENTER,
        'n',
        Key::ENTER,
    ]);
    $catalog = interactiveWizardCoverageCatalog();
    $selection = (new SelectionResolver($catalog))->defaults();
    $callbackSelection = null;

    $result = (new InteractiveWizard($catalog, new SelectionResolver($catalog)))->collect(
        $selection,
        function (ProjectSelection $callbackValue) use (&$callbackSelection): void {
            $callbackSelection = $callbackValue;
        },
    );

    expect($callbackSelection)->toBeInstanceOf(ProjectSelection::class)
        ->and($callbackSelection?->projectName)->toBe('vendor/demo')
        ->and($result->projectName)->toBe('vendor/demo')
        ->and($result->profile)->toBe('full')
        ->and($result->profileGroups)->toBe(['frontend' => 'luma'])
        ->and($result->disabledSets)->toBe([])
        ->and($result->enabledSets)->toBe([])
        ->and($result->enabledAddons)->toBe([])
        ->and($result->install)->toBeFalse();
});

it('collects an additive selection without optional add-ons', function (): void {
    Prompt::fake([
        ...str_split('vendor/minimal'),
        Key::ENTER,
        Key::ENTER,
        Key::ENTER,
        Key::DOWN,
        Key::SPACE,
        Key::ENTER,
        Key::ENTER,
    ]);
    $catalog = interactiveWizardCoverageCatalog(withAddons: false, withProfileGroups: false);
    $selection = new ProjectSelection(
        release: '2.0',
        profile: 'full',
        mode: 'additive',
        profileGroups: [],
        enabledSets: ['search'],
        projectName: 'vendor/original',
    );

    $result = (new InteractiveWizard($catalog, new SelectionResolver($catalog)))->collect($selection);

    expect($result->release)->toBe('2.0')
        ->and($result->mode)->toBe('additive')
        ->and($result->profileGroups)->toBe([])
        ->and($result->disabledSets)->toBe([])
        ->and($result->enabledSets)->toBe(['search', 'catalog'])
        ->and($result->enabledAddons)->toBe([])
        ->and($result->projectName)->toBe('vendor/minimal')
        ->and($result->install)->toBeTrue();
});
