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

it('does not include credential values in handoff output', function (): void {
    $plan = [
        'selection' => ['release' => 'latest', 'profile' => 'mageos-full', 'distribution' => 'standard', 'mode' => 'subtractive'],
        'requires' => [],
        'replaces' => [],
        'follow_up_actions' => ['Credential required for private repository: token'],
    ];

    $output = new HandoffRenderer()->text('/tmp/project', $plan, new InstallationResult('deferred'));

    expect($output)->not->toContain('secret-value')
        ->and($output)->toContain('Credential required');
});
