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

use MageOsInstaller\Presentation\Output\GenerationScreen;
use Symfony\Component\Console\Output\BufferedOutput;

it('renders a bounded generation screen with the latest output', function (): void {
    $output = new BufferedOutput();
    $screen = new GenerationScreen($output, 2);

    $screen->start();
    $screen->processOutput('out', "first\nsecond\nthird\n");
    $screen->finish();

    expect($output->fetch())->toContain('Creating Mage-OS project')
        ->toContain('────────────────')
        ->not->toContain('╭─')
        ->not->toContain('│')
        ->toContain('second')
        ->toContain('third');
});

it('does not present composer status output as an error or duplicate the active stage', function (): void {
    $output = new BufferedOutput();
    $screen = new GenerationScreen($output, 2);

    $screen->step('Downloading the Mage-OS project base');
    $screen->processOutput('err', "Downloading the Mage-OS project base\nCreating a project\n");

    expect($output->fetch())->not->toContain('error:')
        ->toContain('Creating a project');
});
