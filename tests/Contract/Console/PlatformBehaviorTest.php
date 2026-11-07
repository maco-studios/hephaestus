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

it('uses platform-neutral path handling for the current project', function (): void {
    expect(is_dir(getcwd()))->toBeTrue()
        ->and(DIRECTORY_SEPARATOR)->toBeIn(['/', '\\']);
});
