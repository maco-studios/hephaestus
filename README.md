# Mage-OS Installer

Interactive project selection and Composer generation for Mage-OS.

> 🚧 Under construction. Mage-OS Installer is pre-1.0 and its catalog, CLI, and generated project conventions are still evolving. Expect changes between releases and report broken compositions through [GitHub Issues](https://github.com/maco-studios/mage-os-installer/issues).

Mage-OS Installer turns project composition into a terminal wizard. Choose a release, profile, theme, checkout, search engine, feature sets, layers, and add-ons; the installer generates a standalone Mage-OS project with a Composer dependency graph scoped to those choices.

## Quickstart

```bash
composer create-project maco-studios/mage-os-installer my-mageos-project
```

The wizard reviews your selections, generates the project tree, writes a custom `composer.json`, and can install the selected dependencies.

For local development:

```bash
composer install
php bin/mageos new my-mageos-project
```

Useful non-interactive examples:

```bash
php bin/mageos new my-mageos-project --no-interaction --dry-run --format=json
php bin/mageos new my-mageos-project --profile=mageos-minimal --no-install
php bin/mageos new my-mageos-project --profile-group=theme:hyva --enable-addon=hyva --no-install
```

Run `php bin/mageos new --help` for the complete option list.

## Requirements

- PHP 8.2 or newer
- Composer 2.x
- Network access to the configured Composer repositories when installing dependencies
- Credentials for private repositories required by selected add-ons

## Project composition

The wizard composes the generated project from the catalog in `resources/definitions/`:

- Profiles provide starting points such as Full, Lite, Headless, and Minimal.
- Profile groups represent choices such as theme, checkout, and search engine.
- Sets group related Mage-OS modules and optional feature families.
- Layers provide cross-cutting capabilities such as GraphQL, Web API, message queue, and Hyvä compatibility.
- Add-ons integrate third-party packages and repositories.

Subtractive profiles start with the available stock module catalog and replace disabled packages. Additive profiles start with a minimal project and require only the selected module families. The generated `composer.json` reflects the resolved selection instead of depending on an aggregate edition package.

## Documentation

The documentation follows the [Diátaxis](https://diataxis.fr/) framework:

- [Documentation home](docs/README.md)
- [Tutorial: create a project](docs/tutorial-create-a-project.md)
- [How-to guides](docs/how-to.md)
- [CLI and catalog reference](docs/reference.md)
- [Architecture and composition](docs/explanation.md)
- [Contributor development guide](docs/contributing.md)
- [Contributing](CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)

## Development

```bash
composer install
vendor/bin/pest
composer lint
php bin/mageos list
```

The project-generation integration tests cover representative profiles, themes, checkout modes, search engines, tools, add-ons, releases, and additive/subtractive selections. Generated projects are written under `tests/.generated/` and ignored by Git.

## Contributing

Bug reports, catalog corrections, and compatibility improvements are welcome. Include the selected profile, release, distribution, relevant Composer output, and your PHP and Composer versions. Never commit credentials, `auth.json`, or generated `vendor/` trees.

## Credits and attribution

The project-selection concepts, catalog structure, and terminology were informed by [Cresset Tools MageOS Maker](https://github.com/cresset-tools/mageos-maker). Mage-OS Installer is an independent CLI implementation and is not affiliated with or endorsed by Cresset Tools unless explicitly stated.

## License

Mage-OS Installer is available under the [MIT License](LICENSE).
