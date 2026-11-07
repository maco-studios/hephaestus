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

namespace MageOsInstaller\Presentation;

use MageOsInstaller\Application\ProjectFactory;
use MageOsInstaller\Application\Handoff\HandoffRenderer;
use MageOsInstaller\Application\Wizard\InteractiveWizard;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use Symfony\Component\Console\Command\ListCommand as SymfonyListCommand;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Catalog\CatalogRepository;
use MageOsInstaller\Infrastructure\Catalog\DefinitionLoader;
use MageOsInstaller\Infrastructure\Composer\ComposerInstaller;
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;
use MageOsInstaller\Infrastructure\Filesystem\ProjectWriter;
use MageOsInstaller\Infrastructure\Process\ProcessRunner;
use MageOsInstaller\Presentation\Output\ApplicationLogo;
use Symfony\Component\Console\Application;

final class ConsoleApplication extends Application
{
    private readonly ApplicationLogo $logo;

    public function __construct()
    {
        parent::__construct("Mage-OS Installer", "0.0.9");
        $this->logo = new ApplicationLogo();
        $definitions = dirname(__DIR__, 2) . '/resources/definitions';
        $catalog = new CatalogRepository(new DefinitionLoader($definitions))->current();
        $resolver = new SelectionResolver($catalog);
        $factory = new ProjectFactory(
            $resolver,
            new ProjectPlanner($catalog),
            new ProjectWriter(new ComposerJsonRenderer()),
            new ComposerInstaller(new ProcessRunner()),
            'https://repo.mage-os.org/',
        );
        $this->addCommand(
            new NewCommand($catalog, $resolver, $factory, new InteractiveWizard($catalog, $resolver), new HandoffRenderer()),
        );
    }

    public function getLongVersion(): string
    {
        return $this->logo->render($this->getName(), $this->getVersion());
    }

    protected function getDefaultCommands(): array
    {
        return array_map(
            static fn(\Symfony\Component\Console\Command\Command $command): \Symfony\Component\Console\Command\Command => $command instanceof SymfonyListCommand ? new ListCommand() : $command,
            parent::getDefaultCommands(),
        );
    }
}
