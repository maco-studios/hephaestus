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

return [
    'definitions_path' => dirname(__DIR__) . '/resources/definitions',
    'catalog_url' => getenv('HEPHAESTUS_CATALOG_URL') ?: 'https://repo.mage-os.org/packages.json',
    'repository_url' => getenv('HEPHAESTUS_REPOSITORY_URL') ?: 'https://repo.mage-os.org/',
    'cache_dir' => dirname(__DIR__) . '/var/cache/hephaestus',
];
