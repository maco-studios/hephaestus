# Mage-OS Installer

An interactive Composer project factory for Mage-OS.

Mage-OS Installer turns the Mage-OS project-composition model into a terminal wizard. Choose a release, starter profile, theme, checkout, search engine, feature sets, layers, and optional add-ons; the installer then generates a complete Mage-OS project and can install its dependencies.

This project is a CLI-oriented adaptation of the project-selection concepts and catalog used by [Cresset Tools MageOS Maker](https://github.com/cresset-tools/mageos-maker). It is not a Magento module generator: its output is a normal, standalone Mage-OS project.

## Requirements

- PHP 8.2 or newer
- Composer 2.x
- Network access to the configured Composer repositories when installing dependencies
- Mage-OS or third-party repository credentials when a selected add-on requires them

## Create a project

The intended entry point is Composer's `create-project` workflow:

```bash
composer create-project maco-studios/mage-os-installer my-mageos-project
```

The wizard guides you through the available project options, shows a review summary, generates the Mage-OS project structure, and optionally runs Composer installation.

For local development, invoke the command directly:

```bash
php bin/mageos new my-mageos-project
```

Useful non-interactive examples:

```bash
php bin/mageos new my-mageos-project --no-interaction --dry-run --format=json
php bin/mageos new my-mageos-project --profile=mageos-minimal --no-install
php bin/mageos new my-mageos-project --profile-group=theme:hyva --enable-addon=hyva --no-install
```

Use `php bin/mageos new --help` for the complete option list. The generated directory is a real Mage-OS project with the normal Magento directories, a generated `composer.json`, and—when requested—a populated `vendor/` directory. The installer is not required at runtime by the generated project.

## Project composition

The wizard uses the catalog in `resources/definitions/`:

- Profiles provide starting configurations such as Full, Lite, Headless, and Minimal.
- Profile groups represent mutually exclusive choices such as theme, checkout, and search engine.
- Sets group stock Mage-OS functionality.
- Layers represent cross-cutting capabilities such as GraphQL, Web API, message queue, and Hyvä compatibility.
- Add-ons represent optional external or third-party packages.

Selections are resolved before generation. Mandatory dependencies are re-applied when required by another selected feature, and unavailable or incompatible combinations are rejected or adjusted before the project is written.

The installer supports both project strategies used by the catalog:

- Subtractive projects start from a full Mage-OS base and remove disabled, removable functionality.
- Additive projects start from the minimal Mage-OS base and add only selected features.

## Development

```bash
composer install
vendor/bin/pest
composer lint
php bin/mageos list
```

The integration matrix generates real Mage-OS project trees under `tests/.generated/`. Those generated projects are ignored by Git because they can contain a large Composer `vendor/` directory. The optional private Hyvä composition is generated only when the required repository and Composer credentials are configured.

## Repository documentation

- [Documentation home](docs/README.md)
- [Tutorial: create your first Mage-OS project](docs/tutorial-create-a-project.md)
- [How-to guides](docs/how-to.md)
- [CLI and catalog reference](docs/reference.md)
- [Architecture and composition explanation](docs/explanation.md)
- [Contributor development guide](docs/contributing.md)
- [Contributing](CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [MageOS Maker structure research](docs/mageos-maker-structure.md)
- [Composer create-project research](docs/composer-create-project-research.md)

## Credits and attribution

The catalog structure, project-composition concepts, and domain terminology were informed by [Cresset Tools MageOS Maker](https://github.com/cresset-tools/mageos-maker), created and maintained by Cresset Tools and its contributors.

Special credit goes to [Jelle Besseling](https://github.com/jellebesseling), the author identified in the reference repository history, and to the MageOS Maker contributors. This project is an independent CLI implementation and is not affiliated with or endorsed by Cresset Tools unless explicitly stated.

## License

Mage-OS Installer is open-sourced under the [MIT License](LICENSE).
