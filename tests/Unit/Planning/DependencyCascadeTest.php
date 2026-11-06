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
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;

it('represents disabled sets as Composer replacements', function (): void {
    $catalog = (new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->load();
    $plan = (new ProjectPlanner($catalog))->plan(new ProjectSelection('latest', disabledSets: ['luma']));

    expect($plan->replaces)->toHaveKey('mage-os/theme-frontend-luma');
});
