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

use Symfony\Component\Console\Tester\CommandTester;
use Hephaestus\Application\Handoff\HandoffRenderer;
use Hephaestus\Application\ProjectFactory;
use Hephaestus\Application\Wizard\InteractiveWizard;
use Hephaestus\Domain\Planning\ProjectPlanner;
use Hephaestus\Domain\Selection\SelectionResolver;
use Hephaestus\Infrastructure\Catalog\CatalogRepository;
use Hephaestus\Infrastructure\Catalog\DefinitionLoader;
use Hephaestus\Infrastructure\Composer\ComposerInstaller;
use Hephaestus\Infrastructure\Composer\ComposerJsonRenderer;
use Hephaestus\Infrastructure\Filesystem\ProjectWriter;
use Hephaestus\Infrastructure\Process\ProcessRunner;
use Hephaestus\Presentation\NewCommand;

it('does not prompt when input is non-interactive', function (): void {
    $catalog = new CatalogRepository(new DefinitionLoader(__DIR__ . '/../../../resources/definitions'))->current();
    $resolver = new SelectionResolver($catalog);
    $command = new NewCommand($catalog, $resolver, new ProjectFactory($resolver, new ProjectPlanner($catalog), new ProjectWriter(new ComposerJsonRenderer()), new ComposerInstaller(new ProcessRunner())), new InteractiveWizard($catalog, $resolver), new HandoffRenderer());
    $tester = new CommandTester($command);

    expect($tester->execute(['target' => sys_get_temp_dir() . '/mageos-non-interactive', '--dry-run' => true], ['interactive' => false]))->toBe(0);
});
