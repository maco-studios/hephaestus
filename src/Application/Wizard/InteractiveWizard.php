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

namespace MageOsInstaller\Application\Wizard;

use MageOsInstaller\Domain\Catalog\Catalog;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

final class InteractiveWizard
{
    public function __construct(
        private readonly Catalog $catalog,
        private readonly SelectionResolver $resolver,
    ) {}

    public function collect(ProjectSelection $selection): ProjectSelection
    {
        PromptTheme::register();
        $releases = array_combine($this->catalog->availableReleases(), $this->catalog->availableReleases());
        $release = select(
            'Mage-OS release',
            $releases,
            $selection->release,
            info: static fn($value): string => $value === null ? '' : "Generate a project using Mage-OS release {$value}.",
        );
        $profiles = [];
        $profileDescriptions = [];
        foreach ($this->catalog->profiles as $name => $profile) {
            $profiles[$name] = PromptOptionLabels::short(
                ($profile['label'] ?? $name) . ' — ' . ($profile['description'] ?? ''),
            );
            $profileDescriptions[$name] = PromptOptionLabels::description($profile);
        }
        $profile = select(
            'Starter profile',
            $profiles,
            $selection->profile ?? $this->catalog->defaultProfile(),
            info: static fn($value): string => $profileDescriptions[$value] ?? '',
        );
        $selection = $this->resolver->resolve($this->resolver->merge(
            $selection,
            ['profile' => $profile, ...($this->catalog->profiles[$profile]['selection'] ?? [])],
        ));

        $groups = $selection->profileGroups;
        foreach ($this->catalog->profileGroups as $name => $definition) {
            $options = [];
            foreach ($definition['options'] ?? [] as $option) {
                $options[$option['name']] = PromptOptionLabels::definition($option, $option['name']);
            }
            if ($options !== []) {
                $descriptions = [];
                foreach ($definition['options'] ?? [] as $option) {
                    $descriptions[$option['name']] = PromptOptionLabels::description($option);
                }
                $groups[$name] = select(
                    $definition['label'] ?? ucfirst($name),
                    $options,
                    $groups[$name] ?? array_key_first($options),
                    info: static fn($value): string => $descriptions[$value] ?? '',
                );
            }
        }

        $selection = $this->resolver->resolve(new ProjectSelection(
            release: $release,
            distribution: $selection->distribution,
            profile: $profile,
            mode: $selection->mode,
            profileGroups: $groups,
            disabledSets: $selection->disabledSets,
            enabledSets: $selection->enabledSets,
            disabledLayers: $selection->disabledLayers,
            enabledLayers: $selection->enabledLayers,
            enabledAddons: $selection->enabledAddons,
            forcedSets: $selection->forcedSets,
            forcedLayers: $selection->forcedLayers,
            forcedAddons: $selection->forcedAddons,
        ));

        $sets = [];
        foreach ($this->catalog->sets as $name => $definition) {
            $sets[$name] = PromptOptionLabels::definition($definition, $name);
        }
        $setOptions = array_diff_key($sets, array_flip($selection->forcedSets));
        $setDescriptions = [];
        foreach ($this->catalog->sets as $name => $definition) {
            $setDescriptions[$name] = PromptOptionLabels::description($definition);
        }
        $defaultSets = $selection->isAdditive()
            ? array_values(array_intersect(array_keys($setOptions), $selection->enabledSets))
            : array_values(array_diff(array_keys($setOptions), $selection->disabledSets));
        $kept = multiselect(
            $selection->isAdditive() ? 'Features to add to the minimal Mage-OS base' : 'Stock feature sets to keep enabled',
            $setOptions,
            $defaultSets,
            scroll: 12,
            info: static fn($value): string => $setDescriptions[$value] ?? '',
        );

        $addons = [];
        foreach ($this->catalog->addons as $name => $definition) {
            $addons[$name] = PromptOptionLabels::definition($definition, $name);
        }
        $addonDescriptions = [];
        foreach ($this->catalog->addons as $name => $definition) {
            $addonDescriptions[$name] = PromptOptionLabels::description($definition);
        }
        $enabledAddons = $addons === [] ? [] : multiselect(
            'Optional add-ons',
            $addons,
            $selection->enabledAddons,
            scroll: 10,
            info: static fn($value): string => $addonDescriptions[$value] ?? '',
        );
        $install = confirm('Install dependencies after generation?', true);

        return new ProjectSelection(
            release: $release,
            distribution: $selection->distribution,
            profile: $profile,
            mode: $selection->mode,
            profileGroups: $groups,
            disabledSets: $selection->isAdditive() ? [] : array_values(array_diff(array_keys($setOptions), $kept)),
            enabledSets: $selection->isAdditive()
                ? array_values(array_unique(array_merge($kept, $selection->forcedSets)))
                : array_values(array_unique(array_merge($selection->enabledSets, $selection->forcedSets))),
            disabledLayers: $selection->disabledLayers,
            enabledLayers: array_values(array_unique(array_merge($selection->enabledLayers, $selection->forcedLayers))),
            enabledAddons: array_values(array_unique(array_merge($enabledAddons, $selection->forcedAddons))),
            disabledSubtoggles: $selection->disabledSubtoggles,
            enabledOptionSubtoggles: $selection->enabledOptionSubtoggles,
            optionVariants: $selection->optionVariants,
            forcedSets: $selection->forcedSets,
            forcedLayers: $selection->forcedLayers,
            forcedAddons: $selection->forcedAddons,
        );
    }
}
