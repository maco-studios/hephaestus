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

use Hephaestus\Application\Handoff\HandoffRenderer;
use Hephaestus\Domain\Installation\InstallationResult;

it('renders every non-ready status with its status-specific symbol and details', function (): void {
    $plan = [
        'selection' => [
            'release' => '4.0.0',
            'profile' => null,
            'distribution' => 'standard',
            'mode' => 'additive',
        ],
        'requires' => [],
        'replaces' => [],
        'follow_up_actions' => ['Review the generated configuration.'],
    ];

    $renderer = new HandoffRenderer();

    expect($renderer->text('/tmp/planned', $plan, new InstallationResult('planned')))
        ->toContain('Mage-OS project PLANNED')
        ->toContain('<fg=cyan>➜</>')
        ->toContain('Profile:</comment>      custom')
        ->toContain('Next:         Review the generated configuration.');

    expect($renderer->text('/tmp/cancelled', $plan, new InstallationResult('cancelled')))
        ->toContain('Mage-OS project CANCELLED')
        ->toContain('<fg=red>✖</>');

    expect($renderer->text('/tmp/unknown', $plan, new InstallationResult('unexpected')))
        ->toContain('Mage-OS project UNEXPECTED')
        ->toContain('<fg=red>✖</>');
});

it('renders every ready status with the correct next command', function (): void {
    $plan = [
        'selection' => [
            'release' => 'latest',
            'profile' => 'mageos-full',
            'distribution' => 'standard',
            'mode' => 'subtractive',
        ],
        'requires' => [],
        'replaces' => [],
        'follow_up_actions' => ['Configure the store URL.'],
    ];

    $renderer = new HandoffRenderer();

    expect($renderer->text('/tmp/installed', $plan, new InstallationResult('installed')))
        ->toContain('cd</> /tmp/installed')
        ->toContain('bin/magento --version');
    expect($renderer->text('/tmp/generated', $plan, new InstallationResult('generated')))
        ->toContain('cd</> /tmp/generated')
        ->toContain('composer install');
    expect($renderer->text('/tmp/deferred', $plan, new InstallationResult('deferred')))
        ->toContain('cd</> /tmp/deferred')
        ->toContain('composer install')
        ->toContain('Configure the store URL.');
});

it('renders multiple errors and counts selected Composer changes', function (): void {
    $plan = [
        'selection' => [
            'release' => 'latest',
            'profile' => 'mageos-minimal',
            'distribution' => 'standard',
            'mode' => 'subtractive',
            'projectName' => 'acme/storefront',
        ],
        'requires' => ['one' => '*', 'two' => '*'],
        'replaces' => ['three' => '*'],
        'follow_up_actions' => [],
    ];

    $output = new HandoffRenderer()->text(
        '/tmp/failed',
        $plan,
        new InstallationResult('failed', errors: ['First failure', 'Second failure']),
    );

    expect($output)
        ->toContain('Project:</comment>      acme/storefront')
        ->toContain('2 packages, 1 replacements')
        ->toContain('<error>Error: First failure</error>')
        ->toContain('<error>Error: Second failure</error>');
});
