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

namespace MageOsInstaller\Domain\Planning;

use MageOsInstaller\Domain\Catalog\Catalog;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use InvalidArgumentException;

final class ProjectPlanner
{
    public function __construct(private readonly Catalog $catalog) {}

    public function plan(ProjectSelection $selection): ProjectPlan
    {
        $errors = [];
        foreach ($selection->disabledSets as $set) {
            if (!isset($this->catalog->sets[$set])) {
                $errors[] = "Unknown set: {$set}.";
            }
        }
        foreach ($selection->enabledAddons as $addon) {
            if (!isset($this->catalog->addons[$addon])) {
                $errors[] = "Unknown add-on: {$addon}.";
            }
        }
        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        $edition = $selection->isAdditive()
            ? 'mage-os/project-minimal-edition'
            : 'mage-os/project-community-edition';
        $templateKey = $selection->release . ($selection->isAdditive() ? ':minimal' : '');
        $template = $this->catalog->composerTemplates[$templateKey]
            ?? ($selection->release === 'latest'
                ? ($this->catalog->composerTemplates['4.0.0' . ($selection->isAdditive() ? ':minimal' : '')] ?? [])
                : [])
            ?: ($selection->isAdditive()
                ? []
                : ($this->catalog->composerTemplates['latest'] ?? []));
        $requires = is_array($template['require'] ?? null) && $template['require'] !== []
            ? $template['require']
            : [$edition => $selection->release];
        if ($selection->isAdditive()) {
            $requires['laminas/laminas-view'] = '^2.20';
        }
        $replaces = [];
        $repositories = [['type' => 'composer', 'url' => 'https://repo.mage-os.org/']];
        $warnings = [];
        $notes = [];

        foreach ($selection->enabledAddons as $addonName) {
            $addon = $this->catalog->addons[$addonName];
            foreach ($addon['packages'] ?? [] as $package) {
                $name = is_string($package) ? $package : ($package['name'] ?? null);
                if ($name !== null) {
                    $requires[$name] = '*';
                }
            }
            $repositories = array_merge($repositories, $addon['repositories'] ?? []);
            $notes = array_merge($notes, $addon['notes'] ?? []);
        }

        foreach ($selection->enabledLayers as $layerName) {
            $layer = $this->catalog->layers[$layerName] ?? null;
            if ($layer === null) {
                throw new InvalidArgumentException("Unknown layer: {$layerName}.");
            }
            if (($layer['stock'] ?? true) === true && !$selection->isAdditive()) {
                continue;
            }
            foreach ($layer['packages'] ?? [] as $package) {
                $name = is_string($package) ? $package : ($package['name'] ?? null);
                if ($name !== null) {
                    $requires[$name] = '*';
                }
            }
        }

        if ($selection->isAdditive()) {
            foreach ($selection->enabledSets as $setName) {
                $set = $this->catalog->sets[$setName] ?? null;
                if ($set === null) {
                    throw new InvalidArgumentException("Unknown set: {$setName}.");
                }
                foreach ($set['packages'] ?? [] as $package) {
                    $name = is_string($package) ? $package : ($package['name'] ?? null);
                    if ($name !== null) {
                        $requires[$name] = '*';
                    }
                }
            }
        }

        foreach ($selection->disabledSets as $setName) {
            foreach ($this->catalog->sets[$setName]['packages'] ?? [] as $package) {
                if (is_array($package) && ($package['replace'] ?? true) === false) {
                    continue;
                }
                $name = is_string($package) ? $package : ($package['name'] ?? null);
                if ($name !== null) {
                    $replaces[$name] = '*';
                }
            }
        }

        foreach ($selection->disabledLayers as $layerName) {
            foreach ($this->catalog->layers[$layerName]['packages'] ?? [] as $package) {
                if (is_array($package) && ($package['replace'] ?? true) === false) {
                    continue;
                }
                $name = is_string($package) ? $package : ($package['name'] ?? null);
                if ($name !== null) {
                    $replaces[$name] = '*';
                }
            }
        }

        $repositories = array_values(array_unique($repositories, SORT_REGULAR));
        ksort($requires);
        ksort($replaces);

        $baseProject = $this->baseProject($template, $edition);

        return new ProjectPlan(
            selection: $selection,
            baseProject: $baseProject,
            requires: $requires,
            replaces: $replaces,
            repositories: $repositories,
            warnings: $warnings,
            followUpActions: array_values(array_unique($notes)),
            catalogSource: $this->catalog->sourceVersion,
        );
    }

    private function baseProject(array $template, string $edition): array
    {
        foreach (['name', 'version', 'version_normalized', 'dist', 'source', 'time', 'uid'] as $key) {
            unset($template[$key]);
        }

        $template['name'] = $edition;
        $template['description'] ??= 'Mage-OS project generated by the Mage-OS Installer';
        $template['type'] ??= 'project';

        return $template;
    }
}
