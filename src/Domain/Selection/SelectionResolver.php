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

namespace MageOsInstaller\Domain\Selection;

use MageOsInstaller\Domain\Catalog\Catalog;

final class SelectionResolver
{
    public function __construct(private readonly Catalog $catalog) {}

    public function defaults(?string $release = null): ProjectSelection
    {
        $release ??= $this->catalog->availableReleases()[0];
        $profile = $this->catalog->defaultProfile();
        $groups = [];
        foreach ($this->catalog->profileGroups as $name => $_definition) {
            $default = $this->catalog->defaultGroupOption($name);
            if ($default !== null) {
                $groups[$name] = $default;
            }
        }

        $selection = new ProjectSelection($release, profile: $profile, profileGroups: $groups);
        $profileDefinition = $this->catalog->profiles[$profile]['selection'] ?? [];

        return $this->resolve($this->merge($selection, $profileDefinition));
    }

    public function resolve(ProjectSelection $selection): ProjectSelection
    {
        if ($selection->profile !== null && isset($this->catalog->profiles[$selection->profile]['selection'])) {
            $profileSelection = $this->catalog->profiles[$selection->profile]['selection'];
            // Profile-group choices are user-facing overrides. A profile may
            // seed them during defaults(), but resolving a later explicit
            // selection must not silently replace a user's theme/checkout/search.
            unset($profileSelection['profileGroups']);
            $selection = $this->merge($selection, $profileSelection);
        }

        $resolved = $selection;
        foreach ($selection->profileGroups as $group => $optionName) {
            $definition = $this->catalog->profileGroups[$group] ?? [];
            $option = $this->findOption($definition['options'] ?? [], $optionName);
            if ($option === null || !$this->requirementsSatisfied($option['requires']['profileGroups'] ?? [], $selection->profileGroups)) {
                $fallback = $this->catalog->defaultGroupOption($group);
                if ($fallback !== null) {
                    $resolved = $this->merge($resolved, ['profileGroups' => [$group => $fallback]]);
                }
                continue;
            }
            $resolved = $this->applyEffects($resolved, $option);
        }

        return $this->forceRequiredDependencies($resolved);
    }

    public function merge(ProjectSelection $selection, array $changes): ProjectSelection
    {
        $values = $selection->toArray();
        foreach ($changes as $key => $value) {
            if (array_key_exists($key, $values)) {
                if ($key === 'profileGroups' && is_array($value) && is_array($values[$key])) {
                    $values[$key] = array_merge($values[$key], $value);
                } elseif (is_array($value) && is_array($values[$key])) {
                    $values[$key] = array_values(array_unique(array_merge($values[$key], $value)));
                } else {
                    $values[$key] = $value;
                }
            }
        }

        return new ProjectSelection(...$values);
    }

    public function validate(ProjectSelection $selection): array
    {
        $errors = [];
        if (!in_array($selection->release, $this->catalog->availableReleases(), true)) {
            $errors[] = "Unsupported Mage-OS release: {$selection->release}.";
        }
        if ($selection->profile !== null && !isset($this->catalog->profiles[$selection->profile])) {
            $errors[] = "Unknown profile: {$selection->profile}.";
        }
        foreach ($selection->profileGroups as $group => $option) {
            $options = array_column($this->catalog->profileGroups[$group]['options'] ?? [], 'name');
            if (!in_array($option, $options, true)) {
                $errors[] = "Unknown option {$option} for profile group {$group}.";
            }
        }

        return $errors;
    }

    private function findOption(array $options, string $name): ?array
    {
        foreach ($options as $option) {
            if (($option['name'] ?? null) === $name) {
                return $option;
            }
        }

        return null;
    }

    private function requirementsSatisfied(array $requirements, array $groups): bool
    {
        foreach ($requirements as $group => $option) {
            if (($groups[$group] ?? null) !== $option) {
                return false;
            }
        }

        return true;
    }

    private function applyEffects(ProjectSelection $selection, array $option): ProjectSelection
    {
        $enables = $option['enables'] ?? [];
        $disables = $option['disables'] ?? [];

        return $this->merge($selection, [
            'enabledSets' => $enables['sets'] ?? [],
            'enabledLayers' => $enables['layers'] ?? [],
            'enabledAddons' => $enables['addons'] ?? [],
            'disabledSets' => $disables['sets'] ?? [],
            'disabledLayers' => $disables['layers'] ?? [],
            'forcedSets' => $option['forces']['sets'] ?? [],
            'forcedLayers' => $option['forces']['layers'] ?? [],
            'forcedAddons' => $option['forces']['addons'] ?? [],
        ]);
    }

    private function forceRequiredDependencies(ProjectSelection $selection): ProjectSelection
    {
        $forcedSets = $selection->forcedSets;
        $forcedLayers = $selection->forcedLayers;
        $forcedAddons = $selection->forcedAddons;

        do {
            $before = [$forcedSets, $forcedLayers, $forcedAddons];

            $activeSets = $selection->isAdditive()
                ? $selection->enabledSets
                : array_values(array_diff(array_keys($this->catalog->sets), $selection->disabledSets));
            foreach ($activeSets as $setName) {
                $requires = $this->catalog->sets[$setName]['requires'] ?? [];
                $forcedSets = array_merge($forcedSets, $this->requiredValues($requires, 'set', 'sets'));
                $forcedLayers = array_merge($forcedLayers, $this->requiredValues($requires, 'layer', 'layers'));
                $forcedAddons = array_merge($forcedAddons, $this->requiredValues($requires, 'addon', 'addons'));
            }
            foreach ($selection->profileGroups as $group => $optionName) {
                foreach ($this->catalog->profileGroups[$group]['options'] ?? [] as $option) {
                    if (($option['name'] ?? null) === $optionName) {
                        $forcedSets = array_merge($forcedSets, $option['forces']['sets'] ?? []);
                        $forcedLayers = array_merge($forcedLayers, $option['forces']['layers'] ?? []);
                        $forcedAddons = array_merge($forcedAddons, $option['forces']['addons'] ?? []);
                    }
                }
            }

            $forcedSets = array_values(array_unique($forcedSets));
            $forcedLayers = array_values(array_unique($forcedLayers));
            $forcedAddons = array_values(array_unique($forcedAddons));
        } while ($before !== [$forcedSets, $forcedLayers, $forcedAddons]);

        return new ProjectSelection(
            release: $selection->release,
            distribution: $selection->distribution,
            profile: $selection->profile,
            mode: $selection->mode,
            profileGroups: $selection->profileGroups,
            disabledSets: array_values(array_diff($selection->disabledSets, $forcedSets)),
            enabledSets: array_values(array_unique(array_merge($selection->enabledSets, $forcedSets))),
            disabledLayers: array_values(array_diff($selection->disabledLayers, $forcedLayers)),
            enabledLayers: array_values(array_unique(array_merge($selection->enabledLayers, $forcedLayers))),
            enabledAddons: array_values(array_unique(array_merge($selection->enabledAddons, $forcedAddons))),
            disabledSubtoggles: $selection->disabledSubtoggles,
            enabledOptionSubtoggles: $selection->enabledOptionSubtoggles,
            optionVariants: $selection->optionVariants,
            forcedSets: $forcedSets,
            forcedLayers: $forcedLayers,
            forcedAddons: $forcedAddons,
        );
    }

    private function requiredValues(array $requires, string $singular, string $plural): array
    {
        $value = $requires[$plural] ?? $requires[$singular] ?? [];

        return is_array($value) ? $value : ($value === [] ? [] : [$value]);
    }
}
