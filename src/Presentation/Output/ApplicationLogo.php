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

namespace MageOsInstaller\Presentation\Output;

final class ApplicationLogo
{
    public function render(): string
    {
        return <<<'LOGO'
<fg=cyan>  __  __                    ___  ____
 |  \/  | __ _  __ _  ___     / _ \/ ___|
 | |\/| |/ _` |/ _` |/ _ \   | | | \___ \
 | |  | | (_| | (_| |  __/   | |_| |___) |
 |_|  |_|\__,_|\__, |\___|    \___/|____/
               |___/</>
<fg=cyan>                 MAGE-OS</>
<fg=gray>  Community-driven commerce project installer</>

LOGO;
    }
}
