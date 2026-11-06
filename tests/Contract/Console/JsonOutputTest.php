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

it('defines the documented JSON output keys', function (): void {
    $payload = ['status' => 'planned', 'target' => '/tmp/project', 'selection' => [], 'plan' => [], 'steps' => [], 'errors' => []];

    expect(array_keys($payload))->toBe(['status', 'target', 'selection', 'plan', 'steps', 'errors']);
});
