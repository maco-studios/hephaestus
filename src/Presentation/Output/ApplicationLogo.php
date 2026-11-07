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

final class ApplicationLogo
{
    public function render(string $title = 'HEPHAESTUS', ?string $version = null): string
    {
        $versionSuffix = $version === null ? '' : ' ' . $version;

        return sprintf(<<<'LOGO'
<fg=cyan>%s%s</>
<fg=gray>The forge for standalone Mage-OS projects</>

LOGO, $title, $versionSuffix);
    }
}
