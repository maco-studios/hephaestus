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

namespace MageOsInstaller\Application\Handoff;

use MageOsInstaller\Domain\Installation\InstallationResult;

final class HandoffRenderer
{
    public function text(string $target, array $plan, InstallationResult $result): string
    {
        $selection = $plan['selection'];
        $status = strtoupper($result->status);
        $symbol = $result->isSuccessful() ? '<fg=green>✔</>' : '<fg=red>✖</>';
        $lines = [
            '',
            '<info>╭─ Mage-OS project ' . $status . ' ─────────────────────────────╮</info>',
            '<info>│</info> ' . $symbol . ' <comment>Project:</comment>      ' . ($selection['projectName'] ?? 'generated project'),
            '<info>│</info>   <comment>Target:</comment>       ' . $target,
            '<info>│</info>   <comment>Release:</comment>      ' . $selection['release'],
            '<info>│</info>   <comment>Profile:</comment>      ' . ($selection['profile'] ?? 'custom'),
            '<info>│</info>   <comment>Distribution:</comment> ' . $selection['distribution'],
            '<info>│</info>   <comment>Mode:</comment>         ' . $selection['mode'],
            '<info>│</info>   <comment>Composer:</comment>     ' . count($plan['requires']) . ' packages, ' . count($plan['replaces']) . ' replacements',
        ];
        foreach ($plan['follow_up_actions'] as $note) {
            $lines[] = '  Next:         ' . $note;
        }
        if ($result->errors !== []) {
            foreach ($result->errors as $error) {
                $lines[] = '<error>Error: ' . $error . '</error>';
            }
        }
        if ($result->status === 'deferred') {
            $lines[] = '<info>│</info>   <comment>Next:</comment>         composer install';
        }

        $lines[] = '<info>╰──────────────────────────────────────────────────────╯</info>';
        if ($result->isSuccessful()) {
            $lines[] = '';
            $lines[] = '<fg=green>🎉 Your Mage-OS project is ready!</>';
            $lines[] = '  Start developing:';
            $lines[] = '    cd ' . $target;
            if ($result->status !== 'installed') {
                $lines[] = '    composer install';
            }
            $lines[] = '';
            $lines[] = '  Enjoyed the installer? Star the repository: <href=https://github.com/maco-studios/mage-os-installer>github.com/maco-studios/mage-os-installer</>';
        }

        return implode("\n", $lines) . "\n";
    }
}
