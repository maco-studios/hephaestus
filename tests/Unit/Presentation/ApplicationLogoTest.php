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

use MageOsInstaller\Presentation\Output\ApplicationLogo;

it('renders the Mage-OS terminal wordmark and tagline', function (): void {
    $logo = new ApplicationLogo()->render();

    expect($logo)
        ->toContain('MAGE-OS')
        ->toContain('Community-driven commerce project installer')
        ->toContain('<fg=cyan>')
        ->toContain('</>');
});

it('renders a custom title and version', function (): void {
    $logo = new ApplicationLogo()->render('Mage-OS Installer', '1.2.3');

    expect($logo)->toContain('Mage-OS Installer 1.2.3');
});
