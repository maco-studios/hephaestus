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

use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;

final class PromptTheme
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        Prompt::addTheme('mageos-installer', [
            MultiSelectPrompt::class => SideInfoMultiSelectRenderer::class,
            SelectPrompt::class => SideInfoSelectRenderer::class,
        ]);
        Prompt::theme('mageos-installer');
        self::$registered = true;
    }
}
