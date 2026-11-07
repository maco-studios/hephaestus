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

use Hephaestus\Domain\Installation\InstallationResult;

it('represents deferred installation explicitly', function (): void {
    expect(new InstallationResult('deferred')->isSuccessful())->toBeTrue();
});
