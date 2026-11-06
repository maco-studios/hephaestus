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

namespace MageOsInstaller\Application;

use MageOsInstaller\Domain\Installation\InstallationResult;
use MageOsInstaller\Domain\Planning\ProjectPlanner;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Composer\ComposerInstaller;
use MageOsInstaller\Infrastructure\Filesystem\ProjectWriter;
use MageOsInstaller\Infrastructure\Filesystem\TargetDirectory;

final class ProjectFactory
{
    public function __construct(
        private readonly SelectionResolver $selectionResolver,
        private readonly ProjectPlanner $planner,
        private readonly ProjectWriter $writer,
        private readonly ComposerInstaller $installer,
        private readonly string $repositoryUrl = 'https://repo.mage-os.org/',
    ) {}

    public function plan(ProjectSelection $selection): array
    {
        $selection = $this->selectionResolver->resolve($selection);
        $errors = $this->selectionResolver->validate($selection);
        if ($errors !== []) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        return $this->planner->plan($selection)->toArray();
    }

    public function create(TargetDirectory $target, ProjectSelection $selection, bool $dryRun, bool $install): InstallationResult
    {
        $selection = $this->selectionResolver->resolve($selection);
        $plan = $this->planner->plan($selection);
        if ($dryRun) {
            return new InstallationResult('planned', ['plan' => $plan->toArray()]);
        }

        $target->assertSafe();
        $package = $selection->isAdditive()
            ? 'mage-os/project-minimal-edition'
            : 'mage-os/project-community-edition';
        $bootstrap = $this->installer->createProject($package, $selection->release, $this->repositoryUrl, $target->path);
        if (($bootstrap['exit_code'] ?? 1) !== 0) {
            return new InstallationResult('failed', ['generated' => $target->path], [trim($bootstrap['error'] ?: $bootstrap['output'])]);
        }
        $this->writer->write($target, $plan);
        if (!$install) {
            return new InstallationResult('deferred', ['generated' => $target->path]);
        }

        $result = $this->installer->install($target->path);
        if (($result['exit_code'] ?? 1) !== 0) {
            return new InstallationResult('failed', ['generated' => $target->path], [trim($result['error'] ?: $result['output'])]);
        }

        return new InstallationResult('installed', ['generated' => $target->path, 'composer' => 'installed']);
    }
}
