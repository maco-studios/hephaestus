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
use MageOsInstaller\Infrastructure\Catalog\CatalogRepository;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;
use MageOsInstaller\Infrastructure\Filesystem\TargetDirectory;

it('selects catalog defaults and falls back when definitions are absent', function (): void {
    $catalog = new Catalog(
        profiles: ['minimal' => [], 'full' => ['default' => true]],
        sets: [],
        layers: [],
        profileGroups: [
            'frontend' => ['options' => [['name' => 'luma'], ['name' => 'hyva', 'default' => true]]],
            'empty' => ['options' => []],
        ],
        addons: [],
        releases: ['4.0' => []],
    );

    expect($catalog->defaultProfile())->toBe('full')
        ->and($catalog->defaultGroupOption('frontend'))->toBe('hyva')
        ->and($catalog->defaultGroupOption('empty'))->toBeNull()
        ->and($catalog->defaultGroupOption('missing'))->toBeNull()
        ->and($catalog->availableReleases())->toBe(['4.0']);

    $fallback = new Catalog([], [], [], [], [], []);
    expect($fallback->defaultProfile())->toBe('mageos-full')
        ->and($fallback->availableReleases())->toBe(['latest']);

    $firstOption = new Catalog(
        profiles: ['first' => [], 'second' => []],
        sets: [],
        layers: [],
        profileGroups: ['theme' => ['options' => [['name' => 'first-theme']]]],
        addons: [],
        releases: [],
    );
    expect($firstOption->defaultProfile())->toBe('first')
        ->and($firstOption->defaultGroupOption('theme'))->toBe('first-theme');
});

it('validates and creates target directories', function (): void {
    $path = sys_get_temp_dir() . '/mageos-target-' . bin2hex(random_bytes(4));
    $target = new TargetDirectory($path);

    $target->assertSafe();
    $target->ensureExists();
    $target->assertSafe();

    file_put_contents($path . '/existing.txt', 'data');
    expect(fn(): mixed => $target->assertSafe())->toThrow(RuntimeException::class, 'not empty');
    $target->assertSafe(true);

    unlink($path . '/existing.txt');
    rmdir($path);
});

it('rejects a target path that points to a file', function (): void {
    $path = sys_get_temp_dir() . '/mageos-file-' . bin2hex(random_bytes(4));
    file_put_contents($path, 'data');

    expect(fn(): mixed => new TargetDirectory($path)->assertSafe())
        ->toThrow(RuntimeException::class, 'not a directory');

    unlink($path);
});

it('reports when a target directory cannot be created', function (): void {
    $file = sys_get_temp_dir() . '/mageos-parent-file-' . bin2hex(random_bytes(4));
    file_put_contents($file, 'data');

    expect(fn(): mixed => new TargetDirectory($file . '/child')->ensureExists())
        ->toThrow(RuntimeException::class, 'Unable to create target directory');

    unlink($file);
});

it('caches and refreshes the bundled catalog', function (): void {
    $repository = new CatalogRepository(new DefinitionLoader(__DIR__ . '/../../../resources/definitions'));

    $current = $repository->current();
    expect($repository->current())->toBe($current)
        ->and($repository->refresh()->sourceVersion)->toBeString();
});
