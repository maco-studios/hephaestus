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

namespace MageOsInstaller\Application\Wizard;

final class PromptOptionLabels
{
    public static function short(string $text, int $maxLength = 56): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        $length = self::length($text);
        if ($length <= $maxLength) {
            return $text;
        }

        return self::slice($text, max(1, $maxLength - 1)) . '…';
    }

    public static function definition(array $definition, string $fallback): string
    {
        return self::short((string) ($definition['label'] ?? $fallback));
    }

    public static function description(array $definition): string
    {
        return trim((string) ($definition['description'] ?? ''));
    }

    private static function length(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    }

    private static function slice(string $text, int $length): string
    {
        return function_exists('mb_substr') ? mb_substr($text, 0, $length) : substr($text, 0, $length);
    }
}
