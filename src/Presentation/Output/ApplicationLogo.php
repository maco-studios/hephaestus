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
<fg=red>
                ███               ██
             ██      ██       ██      ██
          █              █ █              █
      ██                                     ██
   ██                                           ██
█               ███              ████             ██
█            ██████   █       █████    █       █████
█         █████████      █ ████████       ██████████</>     %s
<fg=red>█        ██████████       █████████        █████████</>          %s
<fg=red>█        ██████████       █████████        █████████
 ██      ███████████      ████████ ██      ████████
     █   █████        █   █████        █   █████
        ███              ███              ███</>
<fg=cyan>                 MAGE-OS</>
<fg=gray>  Community-driven commerce project installer</>

LOGO;
    }
}
