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

it('preserves the wizard installation preference in the project selection', function (): void {
    $selection = new ProjectSelection('latest', install: false);

    expect($selection->install)->toBeFalse();
});

it('rejects unsupported installation modes', function (): void {
    new \MageOsInstaller\Domain\Selection\ProjectSelection('latest', mode: 'invalid');
})->throws(InvalidArgumentException::class);
