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
    $screen = new GenerationScreen($output, 2, '/tmp/mageos-project');

    $screen->start();
    $screen->processOutput('out', "first\nsecond\nthird\n");
    $screen->finish();

    expect($output->fetch())->toContain('Creating Mage-OS project')
        ->toContain('Target: /tmp/mageos-project')
        ->toContain('────────────────')
        ->not->toContain('╭')
        ->not->toContain('│')
        ->toContain('second')
        ->toContain('third')
        ->not->toContain('Generation complete');
});

it('does not present composer status output as an error or duplicate the active stage', function (): void {
    $output = new BufferedOutput();
    $screen = new GenerationScreen($output, 2);

    $screen->step('Downloading the Mage-OS project base');
    $screen->processOutput('err', "Downloading the Mage-OS project base\nCreating a project\n");

    expect($output->fetch())->not->toContain('error:')
        ->toContain('Creating a project');
});

it('renders useful generation output when terminal decoration is disabled', function (): void {
    $output = new BufferedOutput();
    $output->setDecorated(false);
    $screen = new GenerationScreen($output, 2);

    $screen->start();
    $screen->step('Installing Composer dependencies');

    expect($output->fetch())
        ->toContain('Installing Composer dependencies')
        ->not->toContain("\033[");
});

it('uses line-oriented output for a composer-nested generation', function (): void {
    $output = new BufferedOutput();
    $screen = new GenerationScreen($output, target: '/tmp/mageos-project', live: false);

    $screen->start();
    $screen->step('Installing Composer dependencies');
    $screen->processOutput('out', "Loading package information\nInstalling dependencies\n");
    $screen->finish();

    $display = $output->fetch();
    expect($display)
        ->toContain('Installing Composer dependencies')
        ->toContain('Loading package information')
        ->toContain('Installing dependencies')
        ->not->toContain("\033[");
});
