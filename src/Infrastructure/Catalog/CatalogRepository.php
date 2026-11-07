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

namespace Hephaestus\Infrastructure\Catalog;

use Hephaestus\Domain\Catalog\Catalog;

final class CatalogRepository
{
    private ?Catalog $catalog = null;

    public function __construct(private readonly DefinitionLoader $loader) {}

    public function current(): Catalog
    {
        if ($this->catalog instanceof \Hephaestus\Domain\Catalog\Catalog) {
            return $this->catalog;
        }

        $catalog = $this->loader->load();
        $releaseFile = dirname(__DIR__, 3) . '/resources/catalog/releases.json';
        $releases = is_file($releaseFile) ? json_decode((string) file_get_contents($releaseFile), true) : [];
        return $this->catalog = new Catalog(
            profiles: $catalog->profiles,
            sets: $catalog->sets,
            layers: $catalog->layers,
            profileGroups: $catalog->profileGroups,
            addons: $catalog->addons,
            releases: is_array($releases) ? $releases : [],
            composerTemplates: $catalog->composerTemplates,
            sourceVersion: 'bundled',
        );
    }

    public function refresh(): Catalog
    {
        $catalog = $this->loader->load('bundled-' . date('Y-m-d'));
        $this->catalog = new Catalog(
            profiles: $catalog->profiles,
            sets: $catalog->sets,
            layers: $catalog->layers,
            profileGroups: $catalog->profileGroups,
            addons: $catalog->addons,
            releases: $catalog->releases,
            composerTemplates: $catalog->composerTemplates,
            sourceVersion: $catalog->sourceVersion,
        );

        return $this->catalog;
    }
}
