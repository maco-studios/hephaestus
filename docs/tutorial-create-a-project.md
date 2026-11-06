# Tutorial: create your first Mage-OS project

This tutorial takes you from an empty working directory to a generated Mage-OS project. It uses the interactive wizard so that you can see how profiles, groups, features, and add-ons fit together.

## Prerequisites

Install:

- PHP 8.2 or newer;
- Composer 2.x;
- network access to the configured Composer repositories;
- credentials for any private repository required by a selected add-on.

The installer itself does not provide Magento or Mage-OS credentials.

## Create a project

From the directory where you want the new project, run:

```bash
composer create-project maco-studios/mage-os-installer my-mageos-project
```

Composer downloads the installer as a bootstrap package, starts the wizard, and then replaces the bootstrap contents with the generated standalone Mage-OS project. If you are developing the installer from a checkout, install its dependencies instead:

```bash
composer install
```

## Generate a project from a checkout

Choose a new, empty target directory:

```bash
php bin/mageos new ../my-mageos-project
```

The wizard asks for the release, starting profile, profile-group choices, feature sets, layers, and optional add-ons. Review the plan before confirming generation.

For a first project, begin with the default choices. They provide a useful baseline that you can compare with a later custom composition.

## Understand the result

After generation, the installer reports the target, release, profile, mode, and the number of Composer `require` and `replace` entries. The target contains a normal Mage-OS project, including its generated `composer.json`.

Move into the generated project:

```bash
cd ../my-mageos-project
composer install
```

If the installer completed dependency installation, Composer has already populated `vendor/`. If you used `--no-install`, run the command above yourself.

## Make a deliberate second project

Use a dry run to inspect a composition without writing a project:

```bash
php bin/mageos new ../my-mageos-hyva \
  --profile=mageos-minimal \
  --profile-group=theme:hyva \
  --enable-addon=hyva \
  --dry-run \
  --format=json
```

The JSON plan is useful for comparing choices and for building automation. When the plan is correct, remove `--dry-run` and add `--no-install` if you want to inspect the generated `composer.json` before downloading dependencies.

## Continue development

The installer is a project bootstrap tool, not a runtime dependency. Continue application work in the generated directory. Return to the [how-to guides](how-to.md) when you need to reproduce a composition or automate it, and use the [contributor guide](contributing.md) only when changing this installer repository.
