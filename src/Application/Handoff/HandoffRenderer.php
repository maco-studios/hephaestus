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
        $lines = [
            '',
            '<info>Mage-OS project ' . strtoupper($result->status) . '</info>',
            '  Target:       ' . $target,
            '  Release:      ' . $selection['release'],
            '  Profile:      ' . ($selection['profile'] ?? 'custom'),
            '  Distribution: ' . $selection['distribution'],
            '  Mode:         ' . $selection['mode'],
            '  Requires:     ' . count($plan['requires']),
            '  Replaces:     ' . count($plan['replaces']),
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
            $lines[] = '  Run:          composer install';
        }

        return implode("\n", $lines) . "\n";
    }
}
