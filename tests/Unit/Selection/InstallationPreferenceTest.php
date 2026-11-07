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

use Hephaestus\Domain\Selection\ProjectSelection;

it('preserves the wizard installation preference in the project selection', function (): void {
    $selection = new ProjectSelection('latest', install: false);

    expect($selection->install)->toBeFalse();
});

it('rejects unsupported installation modes', function (): void {
    new \Hephaestus\Domain\Selection\ProjectSelection('latest', mode: 'invalid');
})->throws(InvalidArgumentException::class);
