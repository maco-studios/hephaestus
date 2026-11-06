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

use MageOsInstaller\Domain\Selection\ProjectName;

it('validates Composer project names and derives the directory name', function (): void {
    $name = new ProjectName('acme/storefront');

    expect($name->directoryName())->toBe('storefront');
});

it('rejects project names outside the Composer vendor/name format', function (): void {
    new ProjectName('Storefront');
})->throws(InvalidArgumentException::class);
