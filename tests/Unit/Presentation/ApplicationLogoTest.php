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

use Hephaestus\Presentation\Output\ApplicationLogo;

it('renders the Hephaestus terminal wordmark and tagline', function (): void {
    $logo = new ApplicationLogo()->render();

    expect($logo)
        ->toContain('HEPHAESTUS')
        ->toContain('The forge for standalone Mage-OS projects')
        ->toContain('<fg=cyan>')
        ->toContain('</>');
});

it('renders a custom title and version', function (): void {
    $logo = new ApplicationLogo()->render('Hephaestus', '1.2.3');

    expect($logo)->toContain('Hephaestus 1.2.3');
});
