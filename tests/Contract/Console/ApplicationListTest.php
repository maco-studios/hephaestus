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

use MageOsInstaller\Presentation\ConsoleApplication;
use MageOsInstaller\Presentation\ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

it('builds the application with the branded version output', function (): void {
    $application = new ConsoleApplication();

    expect($application->getLongVersion())
        ->toContain('Mage-OS Installer')
        ->toContain('0.0.9');
});
it('renders the concise installer command list', function (): void {
    $application = new Application('Test', '1.0');
    $application->setAutoExit(false);
    $command = new ListCommand();
    $application->addCommand($command);
    $tester = new CommandTester($command);

    expect($tester->execute([]))->toBe(0);
    expect($tester->getDisplay())
        ->toContain('Usage:')
        ->toContain('Available commands:');
});
