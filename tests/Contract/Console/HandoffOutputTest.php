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

use MageOsInstaller\Application\Handoff\HandoffRenderer;
use MageOsInstaller\Domain\Installation\InstallationResult;

it('reports deferred installation next steps', function (): void {
    $plan = ['selection' => ['release' => 'latest', 'profile' => 'mageos-full', 'distribution' => 'standard', 'mode' => 'subtractive'], 'requires' => [], 'replaces' => [], 'follow_up_actions' => []];
    $output = (new HandoffRenderer())->text('/tmp/project', $plan, new InstallationResult('deferred'));

    expect($output)->toContain('Application ready')
        ->toContain('composer install')
        ->not->toContain('Mage-OS project DEFERRED');
});

it('renders a concise application-ready panel after installation', function (): void {
    $plan = ['selection' => ['release' => 'latest', 'profile' => 'mageos-full', 'distribution' => 'standard', 'mode' => 'subtractive'], 'requires' => [], 'replaces' => [], 'follow_up_actions' => []];
    $output = (new HandoffRenderer())->text('/tmp/project', $plan, new InstallationResult('installed'));

    expect($output)->toContain('Application ready')
        ->toContain('cd</> /tmp/project')
        ->toContain('bin/magento --version')
        ->not->toContain('Mage-OS project INSTALLED')
        ->not->toContain('┌')
        ->not->toContain('│')
        ->not->toContain('Composer:');
});

it('does not report a planned project as ready', function (): void {
    $plan = ['selection' => ['release' => 'latest', 'profile' => 'mageos-full', 'distribution' => 'standard', 'mode' => 'subtractive'], 'requires' => [], 'replaces' => [], 'follow_up_actions' => []];
    $output = (new HandoffRenderer())->text('/tmp/staging', $plan, new InstallationResult('planned'));

    expect($output)->toContain('PLANNED')
        ->and($output)->not->toContain('Your Mage-OS project is ready!');
});
