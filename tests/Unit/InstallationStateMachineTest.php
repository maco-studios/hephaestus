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

use MageOsInstaller\Domain\Installation\InstallationStateMachine;

it('allows generation to be deferred and installed later', function (): void {
    $machine = new InstallationStateMachine();

    expect($machine->transition('planned', 'generated'))->toBe('generated')
        ->and($machine->transition('generated', 'deferred'))->toBe('deferred')
        ->and($machine->transition('deferred', 'installed'))->toBe('installed');
});

it('rejects invalid installation transitions', function (): void {
    (new InstallationStateMachine())->transition('planned', 'installed');
})->throws(InvalidArgumentException::class);
