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

use function Laravel\Prompts\multiselect;

final readonly class FeatureSelectionPrompts
{
    public function __construct(private Catalog $catalog) {}

    public function sets(array $disabled): array
    {
        $options = [];
        foreach ($this->catalog->sets as $name => $definition) {
            $options[$name] = PromptOptionLabels::definition($definition, $name);
        }

        $descriptions = [];
        foreach ($this->catalog->sets as $name => $definition) {
            $descriptions[$name] = PromptOptionLabels::description($definition);
        }

        PromptTheme::register();

        return array_values(array_diff(array_keys($options), multiselect(
            'Stock feature sets to keep enabled',
            $options,
            array_values(array_diff(array_keys($options), $disabled)),
            info: static fn($value): string => $descriptions[$value] ?? '',
        )));
    }
}
