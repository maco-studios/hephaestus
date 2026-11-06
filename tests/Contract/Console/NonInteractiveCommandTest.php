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

use Symfony\Component\Console\Tester\CommandTester;
use MageOsInstaller\Application\Handoff\HandoffRenderer;
use MageOsInstaller\Application\ProjectFactory;
use MageOsInstaller\Application\Wizard\InteractiveWizard;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\CatalogRepository;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;
use MageOsInstaller\Infrastructure\Composer\ComposerInstaller;
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;
use MageOsInstaller\Infrastructure\Filesystem\ProjectWriter;
use MageOsInstaller\Infrastructure\Process\ProcessRunner;
use MageOsInstaller\Presentation\NewCommand;

it('does not prompt when input is non-interactive', function (): void {
    $catalog = (new CatalogRepository(new DefinitionLoader(__DIR__ . '/../../../resources/definitions')))->current();
    $resolver = new SelectionResolver($catalog);
    $command = new NewCommand($catalog, $resolver, new ProjectFactory($resolver, new ProjectPlanner($catalog), new ProjectWriter(new ComposerJsonRenderer()), new ComposerInstaller(new ProcessRunner())), new InteractiveWizard($catalog, $resolver), new HandoffRenderer());
    $tester = new CommandTester($command);

    expect($tester->execute(['target' => sys_get_temp_dir() . '/mageos-non-interactive', '--dry-run' => true], ['interactive' => false]))->toBe(0);
});
