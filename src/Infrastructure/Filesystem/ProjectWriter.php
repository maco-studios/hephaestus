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

namespace MageOsInstaller\Infrastructure\Filesystem;

use MageOsInstaller\Domain\Planning\ProjectPlan;
use MageOsInstaller\Infrastructure\Composer\ComposerJsonRenderer;

final readonly class ProjectWriter
{
    public function __construct(private ComposerJsonRenderer $renderer) {}

    public function write(TargetDirectory $target, ProjectPlan $plan): void
    {
        $target->ensureExists();
        file_put_contents($target->path . '/composer.json', $this->renderer->render($plan));

        // create-project may have left a lock file for the aggregate edition.
        // It is invalid after replacing that edition with the selected module
        // graph, so Composer must resolve the generated project from scratch.
        $lockFile = $target->path . '/composer.lock';
        if (is_file($lockFile)) {
            unlink($lockFile);
        }
    }
}
