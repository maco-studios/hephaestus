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
    $result = new ProcessRunner()->run(['php', '-r', 'echo "ok";'], getcwd());

    expect($result['exit_code'])->toBe(0)->and($result['output'])->toBe('ok');
});

it('streams process output to a verbose observer', function (): void {
    $chunks = [];
    $result = new ProcessRunner()->run(
        ['php', '-r', 'echo "out"; fwrite(STDERR, "err");'],
        getcwd(),
        static function (string $type, string $buffer) use (&$chunks): void {
            $chunks[] = [$type, $buffer];
        },
    );

    expect($result['exit_code'])->toBe(0)
        ->and($chunks)->toHaveCount(2)
        ->and(implode('', array_column($chunks, 1)))->toBe('outerr');
});
