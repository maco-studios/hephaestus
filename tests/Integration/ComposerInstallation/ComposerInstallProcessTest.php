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

use MageOsInstaller\Infrastructure\Process\ProcessRunner;

it('captures process exit status and output', function (): void {
    $result = (new ProcessRunner())->run(['php', '-r', 'echo "ok";'], getcwd());

    expect($result['exit_code'])->toBe(0)->and($result['output'])->toBe('ok');
});
