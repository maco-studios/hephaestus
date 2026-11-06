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
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;

it('renders a distinct module-scoped composer document for each representative selection', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $resolver = new SelectionResolver($catalog);
    $planner = new ProjectPlanner($catalog);
    $renderer = new ComposerJsonRenderer();

    $cases = [
        'full-standard' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full'),
            'required' => ['mage-os/theme-frontend-luma', 'mage-os/module-graph-ql', 'mage-os/module-wishlist'],
            'forbidden' => [],
        ],
        'full-hyva' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', profileGroups: ['theme' => 'hyva']),
            'required' => ['hyva-themes/magento2-default-theme', 'mage-os/module-graph-ql'],
            'forbidden' => [],
        ],
        'full-hyva-checkout' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', profileGroups: [
                'theme' => 'hyva',
                'checkout' => 'hyva-checkout',
            ]),
            'required' => ['hyva-themes/magento2-default-theme', 'hyva-themes/magento2-hyva-checkout'],
            'forbidden' => [],
        ],
        'full-open-search' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', profileGroups: ['search' => 'opensearch']),
            'required' => ['mage-os/module-graph-ql'],
            'forbidden' => ['mage-os/module-elasticsearch-8'],
        ],
        'full-elasticsearch' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', profileGroups: ['search' => 'elasticsearch8']),
            'required' => ['mage-os/module-elasticsearch-8'],
            'forbidden' => [],
        ],
        'full-without-storefront' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', disabledSets: ['luma']),
            'required' => ['mage-os/module-graph-ql'],
            'forbidden' => ['mage-os/theme-frontend-luma'],
        ],
        'full-without-tools' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', disabledSets: ['catalog-widget', 'wishlist']),
            'required' => ['mage-os/theme-frontend-luma'],
            'forbidden' => ['mage-os/module-catalog-widget', 'mage-os/module-wishlist'],
        ],
        'full-with-email-tool' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', enabledAddons: ['yireo-emailtester']),
            'required' => ['yireo/magento2-emailtester2'],
            'forbidden' => [],
        ],
        'full-with-nebula' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', enabledAddons: ['nebula']),
            'required' => ['nebula/admin'],
            'forbidden' => [],
        ],
        'full-with-message-queue' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-full', enabledLayers: ['message-queue']),
            'required' => ['mage-os/module-message-queue', 'mage-os/framework-message-queue'],
            'forbidden' => [],
        ],
        'lite' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-lite'),
            'required' => ['mage-os/theme-frontend-luma'],
            'forbidden' => ['mage-os/module-wishlist', 'mage-os/module-graph-ql'],
        ],
        'headless' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-headless'),
            'required' => ['mage-os/module-graph-ql'],
            'forbidden' => ['mage-os/theme-frontend-luma'],
        ],
        'minimal-empty' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal'),
            'required' => ['laminas/laminas-view'],
            'forbidden' => ['mage-os/theme-frontend-luma', 'mage-os/module-wishlist'],
        ],
        'minimal-catalog' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', enabledSets: ['catalog-widget']),
            'required' => ['mage-os/module-catalog-widget'],
            'forbidden' => ['mage-os/module-wishlist', 'mage-os/product-minimal-edition'],
        ],
        'minimal-tools' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', enabledSets: ['catalog-widget', 'n98-magerun']),
            'required' => ['mage-os/module-catalog-widget', 'n98/magerun2-dist'],
            'forbidden' => ['mage-os/module-wishlist'],
        ],
        'minimal-headless-tools' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', enabledLayers: ['graphql'], enabledSets: ['catalog-widget']),
            'required' => ['mage-os/module-graph-ql', 'mage-os/module-catalog-widget'],
            'forbidden' => ['mage-os/theme-frontend-luma'],
        ],
        'minimal-hyva' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', profileGroups: ['theme' => 'hyva']),
            'required' => ['hyva-themes/magento2-default-theme'],
            'forbidden' => ['mage-os/theme-frontend-luma'],
        ],
        'minimal-hyva-checkout' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', profileGroups: [
                'theme' => 'hyva',
                'checkout' => 'hyva-checkout',
            ]),
            'required' => ['hyva-themes/magento2-default-theme', 'hyva-themes/magento2-hyva-checkout'],
            'forbidden' => ['mage-os/theme-frontend-luma'],
        ],
        'minimal-language' => [
            'selection' => new ProjectSelection('latest', profile: 'mageos-minimal', enabledSets: ['language-pt_br']),
            'required' => ['mage-os/language-pt_br'],
            'forbidden' => ['mage-os/module-wishlist'],
        ],
        'legacy-release' => [
            'selection' => new ProjectSelection('3.5.0', profile: 'mageos-lite'),
            'required' => ['mage-os/theme-frontend-luma'],
            'forbidden' => ['mage-os/module-wishlist', 'mage-os/module-graph-ql'],
        ],
    ];

    $renderedDocuments = [];
    foreach ($cases as $name => $case) {
        $selection = $resolver->resolve($case['selection']);
        $document = json_decode($renderer->render($planner->plan($selection)), true, 512, JSON_THROW_ON_ERROR);
        $renderedDocuments[$name] = $document['require'];

        foreach ($case['required'] as $package) {
            expect($document['require'], $name)->toHaveKey($package);
        }
        foreach ($case['forbidden'] as $package) {
            expect($document['require'], $name)->not->toHaveKey($package);
        }
        expect($document['require'], $name)
            ->not->toHaveKey('mage-os/product-community-edition')
            ->not->toHaveKey('mage-os/product-minimal-edition');
    }

    expect(count($renderedDocuments))->toBe(20);
    expect($renderedDocuments['full-standard'])->not->toBe($renderedDocuments['lite']);
    expect($renderedDocuments['minimal-empty'])->not->toBe($renderedDocuments['minimal-catalog']);
    expect($renderedDocuments['full-hyva'])->not->toBe($renderedDocuments['full-standard']);
});
