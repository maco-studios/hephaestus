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

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withSkip([
        // Vendored from laravel/prompts (MIT) — see src/Prompts/LICENSE.md.
        // Kept close to upstream so it stays easy to diff/update by hand.
        __DIR__ . '/src/Prompts',
        // The parent constructor assigns the public `new` command name.
        \Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class => [
            __DIR__ . '/src/Presentation/NewCommand.php',
        ],
        // PHP does not allow a nullsafe call to create a first-class callable.
        \Rector\Php80\Rector\Ternary\TernaryToNullsafeCoalesceRector::class => [
            __DIR__ . '/src/Presentation/NewCommand.php',
        ],
        // Keep the explicit nullable check required by the first-class callbacks above.
        \Rector\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector::class => [
            __DIR__ . '/src/Presentation/NewCommand.php',
        ],
        // Keep the constructor's public Catalog parameter for command compatibility.
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector::class => [
            __DIR__ . '/src/Presentation/NewCommand.php',
        ],
        // array_all() requires PHP 8.4; the installer supports PHP 8.2+.
        \Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector::class,
    ]);
