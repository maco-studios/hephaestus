# Mage-OS Installer

[![Coverage](.github/assets/coverage.svg)](https://github.com/maco-studios/mage-os-installer/actions/workflows/tests.yml)
[![Buy Me a Coffee](https://img.shields.io/badge/Buy%20Me%20a%20Coffee-FFDD00?logo=buymeacoffee&logoColor=000000&style=for-the-badge)](https://buymeacoffee.com/macosauresx)

[![Installer preview](https://raw.githubusercontent.com/maco-studios/mage-os-installer/master/.github/assets/video/preview.gif)](https://raw.githubusercontent.com/maco-studios/mage-os-installer/master/.github/assets/video/preview.mp4)

[Open the full installer video](https://raw.githubusercontent.com/maco-studios/mage-os-installer/master/.github/assets/video/preview.mp4)

Create a tailored, standalone Mage-OS project through an interactive terminal wizard.

> Mage-OS Installer is pre-1.0. Catalog entries, CLI options, and generated-project conventions may change between releases.

## Requirements

- PHP 8.2+
- Composer 2.x
- Network access to the configured Composer repositories
- Credentials for private repositories used by selected add-ons

## Create a project

Run this from the directory where you want the project:

```bash
composer create-project maco-studios/mage-os-installer
```

The wizard asks for the project name, Mage-OS release, profile, theme, checkout, search engine, feature sets, add-ons, and whether to install dependencies. It then generates the project and shows the final project path.

To choose the bootstrap directory explicitly:

```bash
composer create-project maco-studios/mage-os-installer my-mageos-project
```

Answer “No” to dependency installation if you want to review the generated `composer.json` first. Run `composer install` inside the generated project when ready.

## Automation

Use the CLI directly for scripted runs:

```bash
php bin/mageos new my-mageos-project \
  --profile=mageos-minimal \
  --no-install
```

Inspect a composition without writing files:

```bash
php bin/mageos new my-mageos-project \
  --no-interaction \
  --dry-run \
  --format=json
```

Run `php bin/mageos new --help` for all options, including releases, profile groups, disabled sets, and add-ons.

## What it configures

The wizard composes a standalone Mage-OS project from catalog definitions covering:

- starter profiles such as Full, Lite, Headless, and Minimal;
- themes, checkout methods, and search engines;
- feature sets and cross-cutting layers;
- optional third-party add-ons and repositories.

The generated `composer.json` contains the resolved dependencies and replacements for your selections.

## Documentation

- [Documentation home](docs/README.md)
- [Create your first project](docs/tutorial-create-a-project.md)
- [How-to guides](docs/how-to.md)
- [CLI and catalog reference](docs/reference.md)
- [Architecture and composition](docs/explanation.md)

## Contributing

Report bugs and catalog corrections through [GitHub Issues](https://github.com/maco-studios/mage-os-installer/issues). Do not commit credentials, `auth.json`, generated `vendor/` directories, or generated projects.

## License

MIT — see [LICENSE](LICENSE).
