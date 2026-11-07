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

namespace Hephaestus\Presentation\Output;

final class TextOutput
{
    public function line(string $label, string|int $value): string
    {
        return sprintf("  %-14s %s", $label . ':', $value);
    }
}
