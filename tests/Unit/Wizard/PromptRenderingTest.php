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

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
use MageOsInstaller\Application\Wizard\FeatureSelectionPrompts;
use MageOsInstaller\Application\Wizard\PromptTheme;
use MageOsInstaller\Application\Wizard\SideInfoMultiSelectRenderer;
use MageOsInstaller\Application\Wizard\SideInfoSelectRenderer;
use MageOsInstaller\Domain\Catalog\Catalog;

it('collects stock feature sets while honoring disabled sets', function (): void {
    Prompt::fake([Key::ENTER]);
    PromptTheme::register();
    $catalog = new Catalog([], [
        'search' => ['label' => 'Search', 'description' => 'Catalog search'],
        'wishlist' => ['label' => 'Wishlist', 'description' => 'Wishlist support'],
    ], [], [], [], []);

    $selected = (new FeatureSelectionPrompts($catalog))->sets(['wishlist']);

    expect($selected)->toBe(['wishlist']);
});

it('renders select prompts with a side information panel', function (): void {
    Prompt::fake();
    $prompt = new SelectPrompt('Choose a profile', ['full' => 'Full profile', 'minimal' => 'Minimal profile'], info: 'A profile with all Mage-OS features.', hint: 'Use arrows');

    $rendered = (string) (new SideInfoSelectRenderer($prompt))($prompt);

    expect($rendered)->toContain('Choose a profile')
        ->toContain('Details')
        ->toContain('Full profile')
        ->toContain('A profile with all Mage-OS features.')
        ->toContain('Use arrows');
});

it('delegates select rendering when the prompt has no side information or is finished', function (): void {
    Prompt::fake();
    $withoutInfo = new SelectPrompt('Choose', ['one' => 'One'], info: '');
    $finished = new SelectPrompt('Choose', ['one' => 'One'], info: 'Details');
    $finished->state = 'submit';

    expect((string) (new SideInfoSelectRenderer($withoutInfo))($withoutInfo))->toContain('Choose')
        ->and((string) (new SideInfoSelectRenderer($finished))($finished))->toContain('Choose');
});

it('renders multi-select prompts with active and selected states', function (): void {
    Prompt::fake();
    $prompt = new MultiSelectPrompt('Choose features', ['search' => 'Search', 'wishlist' => 'Wishlist'], ['search'], info: 'Select the features to keep.', hint: 'Space toggles');

    $rendered = (string) (new SideInfoMultiSelectRenderer($prompt))($prompt);

    expect($rendered)->toContain('Choose features')
        ->toContain('Details')
        ->toContain('Search')
        ->toContain('Wishlist')
        ->toContain('Select the features to keep.')
        ->toContain('Space toggles');
});

it('delegates multi-select rendering when the prompt has no side information or is finished', function (): void {
    Prompt::fake();
    $withoutInfo = new MultiSelectPrompt('Choose', ['one' => 'One'], info: '');
    $finished = new MultiSelectPrompt('Choose', ['one' => 'One'], info: 'Details');
    $finished->state = 'cancel';

    expect((string) (new SideInfoMultiSelectRenderer($withoutInfo))($withoutInfo))->toContain('Choose')
        ->and((string) (new SideInfoMultiSelectRenderer($finished))($finished))->toContain('Choose');
});
