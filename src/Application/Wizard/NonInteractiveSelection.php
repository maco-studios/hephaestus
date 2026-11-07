<?php

declare(strict_types=1);

/**
 * Copyright (c) 2026 Marcos "Marcão" Aurelio
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/hephaestus
 */

namespace Hephaestus\Application\Wizard;

use Hephaestus\Domain\Selection\ProjectSelection;
use Hephaestus\Presentation\Input\SelectionInput;
use Symfony\Component\Console\Input\InputInterface;

final readonly class NonInteractiveSelection
{
    public function __construct(private SelectionInput $input) {}

    public function collect(ProjectSelection $defaults, InputInterface $input): ProjectSelection
    {
        return $this->input->apply($defaults, $input);
    }
}
