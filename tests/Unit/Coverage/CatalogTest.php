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

it('preserves catalog data and uses the configured source version', function (): void {
    $catalog = new Catalog(
        profiles: ['full' => ['default' => true]],
        sets: ['core' => ['packages' => ['mage-os/core']]],
        layers: ['base' => ['packages' => []]],
        profileGroups: ['frontend' => ['options' => []]],
        addons: ['search' => ['package' => 'elasticsearch']],
        releases: ['4.0.0' => ['stability' => 'stable']],
        composerTemplates: ['default' => ['require' => ['php' => '^8.3']]],
        sourceVersion: 'test-fixture',
    );

    expect($catalog->profiles)->toBe(['full' => ['default' => true]])
        ->and($catalog->sets)->toBe(['core' => ['packages' => ['mage-os/core']]])
        ->and($catalog->layers)->toBe(['base' => ['packages' => []]])
        ->and($catalog->profileGroups)->toBe(['frontend' => ['options' => []]])
        ->and($catalog->addons)->toBe(['search' => ['package' => 'elasticsearch']])
        ->and($catalog->releases)->toBe(['4.0.0' => ['stability' => 'stable']])
        ->and($catalog->composerTemplates)->toBe(['default' => ['require' => ['php' => '^8.3']]])
        ->and($catalog->sourceVersion)->toBe('test-fixture');
});

it('resolves the default profile before falling back to the first profile', function (): void {
    $configured = new Catalog(
        profiles: ['minimal' => [], 'full' => ['default' => true]],
        sets: [],
        layers: [],
        profileGroups: [],
        addons: [],
        releases: [],
    );
    $first = new Catalog(
        profiles: ['minimal' => [], 'full' => []],
        sets: [],
        layers: [],
        profileGroups: [],
        addons: [],
        releases: [],
    );
    $empty = new Catalog([], [], [], [], [], []);

    expect($configured->defaultProfile())->toBe('full')
        ->and($first->defaultProfile())->toBe('minimal')
        ->and($empty->defaultProfile())->toBe('mageos-full');
});

it('resolves group defaults, first options, and missing option names', function (): void {
    $catalog = new Catalog(
        profiles: [],
        sets: [],
        layers: [],
        profileGroups: [
            'defaulted' => ['options' => [
                ['name' => 'first'],
                ['name' => 'preferred', 'default' => true],
            ]],
            'first' => ['options' => [['name' => 'only-choice']]],
            'unnamed-default' => ['options' => [['default' => true]]],
            'empty' => ['options' => []],
        ],
        addons: [],
        releases: [],
    );

    expect($catalog->defaultGroupOption('defaulted'))->toBe('preferred')
        ->and($catalog->defaultGroupOption('first'))->toBe('only-choice')
        ->and($catalog->defaultGroupOption('unnamed-default'))->toBeNull()
        ->and($catalog->defaultGroupOption('empty'))->toBeNull()
        ->and($catalog->defaultGroupOption('missing'))->toBeNull();
});

it('returns configured release names or the latest fallback', function (): void {
    $configured = new Catalog([], [], [], [], [], ['4.0.0' => [], '4.1.0' => []]);
    $empty = new Catalog([], [], [], [], [], []);

    expect($configured->availableReleases())->toBe(['4.0.0', '4.1.0'])
        ->and($empty->availableReleases())->toBe(['latest']);
});
