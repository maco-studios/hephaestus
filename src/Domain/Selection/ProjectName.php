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

namespace Hephaestus\Domain\Selection;

use InvalidArgumentException;

final readonly class ProjectName
{
    public function __construct(public string $value)
    {
        if (self::error($value) !== null) {
            throw new InvalidArgumentException(self::error($value));
        }
    }

    public static function error(string $value): ?string
    {
        if (preg_match('/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/', $value) !== 1) {
            return 'Use the format vendor/project-name with lowercase letters, numbers, dots, dashes, or underscores.';
        }

        return null;
    }

    public function directoryName(): string
    {
        return substr($this->value, strpos($this->value, '/') + 1);
    }
}
