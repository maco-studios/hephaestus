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

namespace MageOsInstaller\Presentation;

use MageOsInstaller\Application\Handoff\HandoffRenderer;
use MageOsInstaller\Application\ProjectFactory;
use MageOsInstaller\Application\Wizard\InteractiveWizard;
use MageOsInstaller\Domain\Catalog\Catalog;
use MageOsInstaller\Domain\Selection\ProjectSelection;
use MageOsInstaller\Domain\Selection\SelectionResolver;
use MageOsInstaller\Infrastructure\Filesystem\TargetDirectory;
use MageOsInstaller\Presentation\Output\ApplicationLogo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class NewCommand extends Command
{
    public function __construct(
        private readonly Catalog $catalog,
        private readonly SelectionResolver $resolver,
        private readonly ProjectFactory $factory,
        private readonly InteractiveWizard $wizard,
        private readonly HandoffRenderer $handoff,
        private readonly ApplicationLogo $logo = new ApplicationLogo(),
    ) {
        parent::__construct('new');
        $this->setDescription('Create a tailored Mage-OS project.');
    }

    protected function configure(): void
    {
        $this->addArgument('target', InputArgument::OPTIONAL, 'Target directory', '.')
            ->addOption('release', null, InputOption::VALUE_REQUIRED)
            ->addOption('distribution', null, InputOption::VALUE_REQUIRED, 'Mage-OS distribution', 'standard')
            ->addOption('profile', null, InputOption::VALUE_REQUIRED)
            ->addOption('mode', null, InputOption::VALUE_REQUIRED)
            ->addOption('profile-group', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED)
            ->addOption('disable-set', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED)
            ->addOption('enable-addon', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
            ->addOption('no-install', null, InputOption::VALUE_NONE)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'text or json', 'text')
            ->addOption('force', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($input->isInteractive() && $input->getOption('format') !== 'json') {
                $output->write($this->logo->render());
            }

            $selection = $this->resolver->defaults($input->getOption('release'));
            $selection = $this->fromOptions($selection, $input);
            if ($input->isInteractive()) {
                $selection = $this->wizard->collect($selection);
            }
            $plan = $this->factory->plan($selection);
            if (!$input->getOption('dry-run') && $input->isInteractive()) {
                $output->writeln($this->handoff->text((string) $input->getArgument('target'), $plan, new \MageOsInstaller\Domain\Installation\InstallationResult('planned')));
                if (!\Symfony\Component\Console\Question\ConfirmationQuestion::class) {
                    return Command::FAILURE;
                }
                $helper = $this->getHelper('question');
                $question = new \Symfony\Component\Console\Question\ConfirmationQuestion('Generate this project? [y/N] ', false);
                if (!$helper->ask($input, $output, $question)) {
                    return 3;
                }
            }

            $target = new TargetDirectory($this->absolutePath((string) $input->getArgument('target')));
            $result = $this->factory->create($target, $selection, (bool) $input->getOption('dry-run'), !(bool) $input->getOption('no-install'));
            if ($input->getOption('format') === 'json') {
                $output->writeln(json_encode(['status' => $result->status, 'target' => $target->path, 'selection' => $plan['selection'], 'plan' => $plan, 'steps' => $result->steps, 'errors' => $result->errors], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $output->write($this->handoff->text($target->path, $plan, $result));
            }

            return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $exception) {
            if ($input->getOption('format') === 'json') {
                $output->writeln(json_encode(['status' => 'failed', 'errors' => [$exception->getMessage()]], JSON_PRETTY_PRINT));
            } else {
                $output->writeln('<error>' . $exception->getMessage() . '</error>');
            }

            return Command::FAILURE;
        }
    }

    private function fromOptions(ProjectSelection $selection, InputInterface $input): ProjectSelection
    {
        $groups = $selection->profileGroups;
        foreach ($input->getOption('profile-group') as $pair) {
            [$group, $option] = array_pad(explode(':', $pair, 2), 2, null);
            if ($group && $option) {
                $groups[$group] = $option;
            }
        }

        return new ProjectSelection(
            release: $selection->release,
            distribution: (string) $input->getOption('distribution'),
            profile: $input->getOption('profile') ?: $selection->profile,
            mode: $input->getOption('mode') ?: $selection->mode,
            profileGroups: $groups,
            disabledSets: array_values(array_unique(array_merge($selection->disabledSets, $input->getOption('disable-set')))),
            enabledSets: $selection->enabledSets,
            disabledLayers: $selection->disabledLayers,
            enabledLayers: $selection->enabledLayers,
            enabledAddons: array_values(array_unique(array_merge($selection->enabledAddons, $input->getOption('enable-addon')))),
            disabledSubtoggles: $selection->disabledSubtoggles,
            enabledOptionSubtoggles: $selection->enabledOptionSubtoggles,
            optionVariants: $selection->optionVariants,
        );
    }

    private function absolutePath(string $path): string
    {
        if ($path === '.') {
            return getcwd() ?: $path;
        }

        return str_starts_with($path, '/') ? $path : (getcwd() . '/' . $path);
    }
}
