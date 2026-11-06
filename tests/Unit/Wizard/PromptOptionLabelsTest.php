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

use MageOsInstaller\Application\Wizard\PromptOptionLabels;

it('keeps short labels unchanged', function (): void {
    expect(PromptOptionLabels::short('GraphQL API'))->toBe('GraphQL API');
});

it('truncates long prompt labels without changing the source text', function (): void {
    $source = str_repeat('Long feature description ', 10);
    $display = PromptOptionLabels::short($source, 32);

    expect(mb_strlen($display))->toBe(32)
        ->and($display)->toEndWith('…')
        ->and($source)->toContain('Long feature description');
});

it('collapses whitespace before truncating', function (): void {
    expect(PromptOptionLabels::short("  Hyvä\n    compatibility   modules  "))
        ->toBe('Hyvä compatibility modules');
});

it('keeps the full description for the side panel', function (): void {
    expect(PromptOptionLabels::description([
        'label' => 'Catalog',
        'description' => 'Full catalog functionality with product and category management.',
    ]))->toBe('Full catalog functionality with product and category management.');
});

it('does not invent a description from an option label', function (): void {
    expect(PromptOptionLabels::description(['label' => 'Luma']))->toBe('');
});
