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

namespace Hephaestus\Application;

use Hephaestus\Domain\Installation\InstallationResult;
use Hephaestus\Domain\Planning\ProjectPlanner;
use Hephaestus\Domain\Selection\ProjectSelection;
use Hephaestus\Domain\Selection\SelectionResolver;
use Hephaestus\Infrastructure\Composer\ComposerInstaller;
use Hephaestus\Infrastructure\Filesystem\ProjectWriter;
use Hephaestus\Infrastructure\Filesystem\TargetDirectory;

final readonly class ProjectFactory
{
    public function __construct(
        private SelectionResolver $selectionResolver,
        private ProjectPlanner $planner,
        private ProjectWriter $writer,
        private ComposerInstaller $installer,
        private string $repositoryUrl = 'https://repo.mage-os.org/',
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

    public function create(
        TargetDirectory $target,
        ProjectSelection $selection,
        bool $dryRun,
        bool $install,
        ?callable $onStep = null,
        ?callable $onProcessOutput = null,
    ): InstallationResult {
        if ($onStep !== null) {
            $onStep('Resolving the selected composition');
        }
        $selection = $this->selectionResolver->resolve($selection);
        $plan = $this->planner->plan($selection);
        if ($dryRun) {
            return new InstallationResult('planned', ['plan' => $plan->toArray()]);
        }

        $target->assertSafe();
        $package = $selection->isAdditive()
            ? 'mage-os/project-minimal-edition'
            : 'mage-os/project-community-edition';
        if ($onStep !== null) {
            $onStep('Downloading the Mage-OS project base');
        }
        $bootstrap = $this->installer->createProject($package, $selection->release, $this->repositoryUrl, $target->path, $onProcessOutput);
        if (($bootstrap['exit_code'] ?? 1) !== 0) {
            return new InstallationResult('failed', ['generated' => $target->path], [trim($bootstrap['error'] ?: $bootstrap['output'])]);
        }
        if ($onStep !== null) {
            $onStep('Writing the generated Composer configuration');
        }
        $this->writer->write($target, $plan);
        if (!$install) {
            return new InstallationResult('deferred', ['generated' => $target->path]);
        }

        if ($onStep !== null) {
            $onStep('Installing Composer dependencies');
        }
        $result = $this->installer->install($target->path, $onProcessOutput);
        if (($result['exit_code'] ?? 1) !== 0) {
            return new InstallationResult('failed', ['generated' => $target->path], [trim($result['error'] ?: $result['output'])]);
        }

        return new InstallationResult('installed', ['generated' => $target->path, 'composer' => 'installed']);
    }
}
