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
        if (in_array($result->status, ['installed', 'generated', 'deferred'], true)) {
            return $this->readyPanel($target, $plan, $result);
        }

        $selection = $plan['selection'];
        $status = strtoupper($result->status);
        $symbol = match ($result->status) {
            'installed', 'generated', 'deferred' => '<fg=green>✔</>',
            'planned' => '<fg=cyan>➜</>',
            default => '<fg=red>✖</>',
        };
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
        foreach ($result->errors as $error) {
            $lines[] = '<error>Error: ' . $error . '</error>';
        }
        if ($result->status === 'deferred') {
            $lines[] = '<info>│</info>   <comment>Next:</comment>         composer install';
        }

        $lines[] = '<info>╰──────────────────────────────────────────────────────╯</info>';
        return implode("\n", $lines) . "\n";
    }

    private function readyPanel(string $target, array $plan, InstallationResult $result): string
    {
        $command = $result->status === 'installed' ? 'bin/magento --version' : 'composer install';
        $lines = [
            '',
            '<fg=green;options=bold>✦ Application ready</>',
            '<fg=cyan>→</> You can start your Mage-OS development using:',
            '',
            '<fg=yellow>1.</> <fg=cyan>cd</> ' . $target,
            '<fg=yellow>2.</> <fg=cyan>' . $command . '</>',
        ];

        foreach ($plan['follow_up_actions'] ?? [] as $note) {
            $lines[] = '<fg=yellow>!</> ' . $note;
        }

        $lines[] = '';
        $lines[] = '<fg=magenta>✦</> Build something amazing with Mage-OS!';
        $lines[] = '';
        $lines[] = '<fg=yellow>★</> Enjoyed the installer? Star our repository:';
        $lines[] = '   <href=https://github.com/maco-studios/mage-os-installer>github.com/maco-studios/mage-os-installer</>';

        return implode("\n", $lines) . "\n";
    }
}
