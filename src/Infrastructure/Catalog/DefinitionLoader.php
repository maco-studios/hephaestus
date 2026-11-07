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

namespace MageOsInstaller\Infrastructure\Catalog;

use MageOsInstaller\Domain\Catalog\Catalog;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final readonly class DefinitionLoader
{
    public function __construct(private string $definitionsPath) {}

    public function load(string $sourceVersion = 'bundled'): Catalog
    {
        return new Catalog(
            profiles: $this->readDirectory('profiles'),
            sets: $this->readDirectory('sets'),
            layers: $this->readDirectory('layers'),
            profileGroups: $this->readDirectory('profile-groups', true),
            addons: $this->readDirectory('addons'),
            releases: [],
            composerTemplates: $this->readComposerTemplates(),
            sourceVersion: $sourceVersion,
        );
    }

    private function readComposerTemplates(): array
    {
        $path = dirname($this->definitionsPath) . '/catalog/templates';
        $templates = [];
        foreach (glob($path . '/*.json') ?: [] as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $key = str_starts_with($filename, 'minimal-')
                ? substr($filename, strlen('minimal-')) . ':minimal'
                : $filename;
            $template = json_decode((string) file_get_contents($file), true);
            if (is_array($template)) {
                $templates[$key] = $template;
            }
        }

        return $templates;
    }

    private function readDirectory(string $directory, bool $ordered = false): array
    {
        $path = $this->definitionsPath . '/' . $directory;
        $items = [];
        foreach (glob($path . '/*.yaml') ?: [] as $file) {
            $definition = Yaml::parseFile($file);
            if (!is_array($definition) || !isset($definition['name'])) {
                throw new RuntimeException("Definition {$file} is missing a name.");
            }
            $items[$definition['name']] = $definition;
        }

        if ($ordered) {
            uasort($items, static fn(array $a, array $b): int => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));
        } else {
            ksort($items);
        }

        return $items;
    }
}
