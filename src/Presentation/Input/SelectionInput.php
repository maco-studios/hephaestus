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

namespace MageOsInstaller\Presentation\Input;

use MageOsInstaller\Domain\Selection\ProjectSelection;
use Symfony\Component\Console\Input\InputInterface;

final class SelectionInput
{
    public function apply(ProjectSelection $selection, InputInterface $input): ProjectSelection
    {
        $groups = $selection->profileGroups;
        foreach ($input->getOption('profile-group') as $pair) {
            [$group, $option] = array_pad(explode(':', $pair, 2), 2, null);
            if ($group !== null && $option !== null) {
                $groups[$group] = $option;
            }
        }

        return new ProjectSelection(
            release: $selection->release,
            distribution: (string) $input->getOption('distribution'),
            profile: $input->getOption('profile') ?: $selection->profile,
            mode: $input->getOption('mode') ?: $selection->mode,
            profileGroups: $groups,
            disabledSets: array_values(array_unique(array_merge($selection->disabledSets, $input->getOption('disable-set')))),
            enabledSets: $selection->enabledSets,
            disabledLayers: $selection->disabledLayers,
            enabledLayers: $selection->enabledLayers,
            enabledAddons: array_values(array_unique(array_merge($selection->enabledAddons, $input->getOption('enable-addon')))),
            disabledSubtoggles: $selection->disabledSubtoggles,
            enabledOptionSubtoggles: $selection->enabledOptionSubtoggles,
            optionVariants: $selection->optionVariants,
            projectName: $input->getOption('name') ?: $selection->projectName,
            install: $selection->install,
        );
    }
}
