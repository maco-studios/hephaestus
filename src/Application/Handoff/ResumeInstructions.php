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

namespace Hephaestus\Application\Handoff;

final class ResumeInstructions
{
    public function forStatus(string $status): string
    {
        return match ($status) {
            'deferred' => 'Change into the generated project and run composer install.',
            'failed' => 'Review the reported step, correct the issue, and rerun the installer from the generated project.',
            'cancelled' => 'Rerun the command with the same target after reviewing the selected options.',
            default => 'The generated project is ready for the next Mage-OS setup step.',
        };
    }
}
