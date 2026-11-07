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

namespace Hephaestus\Infrastructure\Composer;

use Hephaestus\Domain\Planning\ProjectPlan;

final class ComposerJsonRenderer
{
    public function render(ProjectPlan $plan): string
    {
        $composer = $plan->baseProject;
        $composer['repositories'] = $plan->repositories;
        $composer['minimum-stability'] ??= 'stable';
        $composer['prefer-stable'] ??= true;
        $composer['config'] ??= [];
        $composer['config']['allow-plugins'] = ($composer['config']['allow-plugins'] ?? []) + [
            'php-http/discovery' => true,
            'mage-os/composer-dependency-version-audit-plugin' => true,
            'mage-os/magento-composer-installer' => true,
            'mage-os/composer-root-update-plugin' => true,
            'mage-os/inventory-composer-installer' => true,
            'dealerdirect/phpcodesniffer-composer-installer' => true,
        ];
        /*
         * Keep the template's complete project metadata and only overlay the
         * selection-specific dependency sections.
         */
        $composer['require'] = $plan->requires;
        /*
         * The plan owns repositories because add-ons and distributions may
         * contribute entries in addition to the bundled project template.
         */
        $composer['repositories'] = $plan->repositories;
        /*
         * `require` and `replace` are intentionally sorted by the planner.
         */
        if ($plan->replaces !== []) {
            $composer['replace'] = $plan->replaces;
        } else {
            unset($composer['replace']);
        }

        // JSON object order is not semantically significant, but a Composer
        // project should be readable in the same canonical shape users expect:
        // identity first, dependencies next, then autoload/configuration and
        // package metadata. Preserve any future template keys after the known
        // Composer sections instead of dropping them.
        $order = [
            'name', 'description', 'type', 'license', 'authors', 'homepage',
            'keywords', 'support', 'require', 'require-dev', 'conflict',
            'replace', 'provide', 'suggest', 'repositories', 'autoload',
            'autoload-dev', 'minimum-stability', 'prefer-stable', 'config',
            'scripts', 'extra', 'bin', 'archive',
        ];
        $ordered = [];
        foreach ($order as $key) {
            if (array_key_exists($key, $composer)) {
                $ordered[$key] = $composer[$key];
            }
        }
        foreach ($composer as $key => $value) {
            if (!array_key_exists($key, $ordered)) {
                $ordered[$key] = $value;
            }
        }
        $composer = $ordered;

        return json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }
}
