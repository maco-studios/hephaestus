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
use MageOsInstaller\Application\ProjectFactory;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Infrastructure\Catalog\CatalogRepository;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;
use MageOsInstaller\Infrastructure\Composer\ComposerInstaller;
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;
use MageOsInstaller\Infrastructure\Filesystem\TargetDirectory;
use MageOsInstaller\Infrastructure\Filesystem\ProjectWriter;
use MageOsInstaller\Infrastructure\Process\ProcessRunner;

it('writes multiple visible Mage-OS project compositions under tests/.generated', function (): void {
    $catalog = new CatalogRepository(new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->current();
    $resolver = new SelectionResolver($catalog);
    $factory = new ProjectFactory(
        $resolver,
        new ProjectPlanner($catalog),
        new ProjectWriter(new ComposerJsonRenderer()),
        new ComposerInstaller(new ProcessRunner()),
    );
    $writer = new ProjectWriter(new ComposerJsonRenderer());
    $installer = new ComposerInstaller(new ProcessRunner());
    $outputRoot = __DIR__ . '/../../.generated';

    $selections = [
        'full' => $resolver->resolve(new ProjectSelection('latest', profile: 'mageos-full')),
        'lite' => $resolver->resolve(new ProjectSelection('latest', profile: 'mageos-lite')),
        'headless' => $resolver->resolve(new ProjectSelection('latest', profile: 'mageos-headless')),
        'minimal' => $resolver->resolve(new ProjectSelection('latest', profile: 'mageos-minimal')),
        'custom-hyva' => $resolver->resolve(new ProjectSelection(
            'latest',
            profile: 'mageos-minimal',
            profileGroups: ['theme' => 'hyva', 'checkout' => 'default', 'search' => 'elasticsearch8'],
            enabledSets: ['catalog-widget'],
        )),
    ];

    $hyvaAvailable = getenv('MAGEOS_HYVA_PROJECT') !== false
        && getenv('MAGEOS_HYVA_PROJECT') !== '';
    if (!$hyvaAvailable) {
        unset($selections['custom-hyva']);
    }

    foreach ($selections as $name => $selection) {
        $targetPath = $outputRoot . '/' . $name;
        $target = new TargetDirectory($targetPath);
        if (is_dir($targetPath . '/app') && is_file($targetPath . '/composer.json')) {
            $resolved = $resolver->resolve($selection);
            $writer->write($target, new ProjectPlanner($catalog)->plan($resolved));
            $install = $installer->install($targetPath);
            $result = new MageOsInstaller\Domain\Installation\InstallationResult(
                ($install['exit_code'] ?? 1) === 0 ? 'installed' : 'failed',
                ['generated' => $targetPath],
                ($install['exit_code'] ?? 1) === 0 ? [] : [trim($install['error'] ?: $install['output'])],
            );
        } else {
            $result = $factory->create($target, $selection, dryRun: false, install: true);
        }

        if ($result->status !== 'installed') {
            throw new RuntimeException("{$name} generation failed: " . implode('; ', $result->errors));
        }
    }

    expect($selections)->toHaveKeys($hyvaAvailable
        ? ['full', 'lite', 'headless', 'minimal', 'custom-hyva']
        : ['full', 'lite', 'headless', 'minimal']);
    foreach (array_keys($selections) as $name) {
        expect($outputRoot . '/' . $name . '/composer.json')->toBeFile();
        expect($outputRoot . '/' . $name . '/app')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/bin')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/pub')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/dev')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/lib')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/var')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/vendor')->toBeDirectory()
            ->and($outputRoot . '/' . $name . '/mageos-installer.json')->not->toBeFile()
            ->and($outputRoot . '/' . $name . '/MAGEOS_SETUP.md')->not->toBeFile();
    }

    $full = json_decode((string) file_get_contents($outputRoot . '/full/composer.json'), true);
    $fullJson = (string) file_get_contents($outputRoot . '/full/composer.json');
    expect($fullJson)->toStartWith("{\n    \"name\":");
    expect($full['require-dev'])->toHaveKey('phpunit/phpunit')
        ->and($full['require'])->toHaveKey('mage-os/module-contact')
        ->and($full['require'])->not->toHaveKey('mage-os/product-community-edition')
        ->and($full['autoload']['psr-0'][''])->toContain('generated/code/')
        ->and($full['autoload']['exclude-from-classmap'])->toContain('**/Test/Unit/**')
        ->and($full['extra']['magento-force'])->toBe('override');

    $minimal = json_decode((string) file_get_contents($outputRoot . '/minimal/composer.json'), true);
    expect($minimal['require']['laminas/laminas-view'])->toBe('^2.20')
        ->and($minimal['require-dev'])->toHaveKey('phpunit/phpunit');
});
