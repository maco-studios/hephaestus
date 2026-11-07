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

use MageOsInstaller\Application\Handoff\ResumeInstructions;
use MageOsInstaller\Application\Wizard\NonInteractiveSelection;
use MageOsInstaller\Application\Wizard\SelectionFeedback;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Presentation\Input\SelectionInput;
use MageOsInstaller\Presentation\Output\JsonOutput;
use MageOsInstaller\Presentation\Output\PlanRenderer;
use MageOsInstaller\Presentation\Output\TextOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

it('provides the next action for each installation status', function (string $status, string $instruction): void {
    expect((new ResumeInstructions())->forStatus($status))->toBe($instruction);
})->with([
    ['deferred', 'Change into the generated project and run composer install.'],
    ['failed', 'Review the reported step, correct the issue, and rerun the installer from the generated project.'],
    ['cancelled', 'Rerun the command with the same target after reviewing the selected options.'],
    ['installed', 'The generated project is ready for the next Mage-OS setup step.'],
]);

it('formats selection feedback', function (): void {
    expect((new SelectionFeedback())->explain('Redis', 'requires the cache layer'))
        ->toBe('Redis: requires the cache layer');
});

it('renders JSON with stable human-readable formatting', function (): void {
    $payload = ['path' => '/tmp/mage-os', 'label' => 'Mage-OS'];

    expect((new JsonOutput())->render($payload))
        ->toBe("{\n    \"path\": \"/tmp/mage-os\",\n    \"label\": \"Mage-OS\"\n}\n");
    expect((new PlanRenderer())->json($payload))
        ->toBe("{\n    \"path\": \"/tmp/mage-os\",\n    \"label\": \"Mage-OS\"\n}\n");
});

it('formats text output labels and values', function (): void {
    expect((new TextOutput())->line('Target', '/tmp/mage-os'))
        ->toBe("  Target:        /tmp/mage-os");
    expect((new TextOutput())->line('Count', 3))
        ->toBe("  Count:         3");
});

it('applies non-interactive command options to the selection', function (): void {
    $defaults = new ProjectSelection(
        release: 'latest',
        profile: 'mageos-full',
        profileGroups: ['frontend' => 'luma'],
        disabledSets: ['search'],
        enabledAddons: ['redis'],
        projectName: 'vendor/default-project',
    );
    $input = new ArrayInput([
        '--distribution' => 'enterprise',
        '--profile' => 'mageos-minimal',
        '--mode' => 'additive',
        '--profile-group' => ['frontend:hyva', 'invalid'],
        '--disable-set' => ['search', 'catalog'],
        '--enable-addon' => ['redis', 'opensearch'],
        '--name' => 'vendor/custom-project',
    ], new InputDefinition([
        new InputOption('distribution', null, InputOption::VALUE_REQUIRED, '', 'standard'),
        new InputOption('profile', null, InputOption::VALUE_REQUIRED),
        new InputOption('mode', null, InputOption::VALUE_REQUIRED),
        new InputOption('profile-group', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, '', []),
        new InputOption('disable-set', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, '', []),
        new InputOption('enable-addon', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, '', []),
        new InputOption('name', null, InputOption::VALUE_REQUIRED),
    ]));

    $selection = (new NonInteractiveSelection(new SelectionInput()))->collect($defaults, $input);

    expect($selection->distribution)->toBe('enterprise')
        ->and($selection->profile)->toBe('mageos-minimal')
        ->and($selection->mode)->toBe('additive')
        ->and($selection->profileGroups)->toBe(['frontend' => 'hyva'])
        ->and($selection->disabledSets)->toBe(['search', 'catalog'])
        ->and($selection->enabledAddons)->toBe(['redis', 'opensearch'])
        ->and($selection->projectName)->toBe('vendor/custom-project');
});
