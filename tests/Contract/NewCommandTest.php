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

it('supports a non-interactive dry run', function (): void {
    $catalog = new CatalogRepository(new DefinitionLoader(__DIR__ . '/../../resources/definitions'))->current();
    $resolver = new SelectionResolver($catalog);
    $command = new NewCommand(
        $catalog,
        $resolver,
        new ProjectFactory($resolver, new ProjectPlanner($catalog), new ProjectWriter(new ComposerJsonRenderer()), new ComposerInstaller(new ProcessRunner())),
        new InteractiveWizard($catalog, $resolver),
        new HandoffRenderer(),
    );
    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        'target' => '.',
        '--name' => 'acme/storefront',
        '--dry-run' => true,
        '--format' => 'json',
    ], ['interactive' => false]);

    expect($exitCode)->toBe(0)
        ->and($tester->getDisplay())->toContain('"status": "planned"')
        ->and($tester->getDisplay())->toContain('"projectName": "acme/storefront"')
        ->and($tester->getDisplay())->toContain('/storefront');
});
