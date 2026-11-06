# Reference

## Command

```text
mageos new [options] [target]
```

Creates a tailored Mage-OS project. The target defaults to the current directory.

### Options

| Option | Meaning |
| --- | --- |
| `--release=RELEASE` | Mage-OS release to use. |
| `--distribution=DISTRIBUTION` | Distribution name; defaults to `standard`. |
| `--profile=PROFILE` | Starting profile. |
| `--mode=MODE` | Composition mode, normally `additive` or `subtractive`. |
| `--profile-group=GROUP:OPTION` | Select a profile-group option; repeatable. |
| `--disable-set=SET` | Disable a feature set; repeatable. |
| `--enable-addon=ADDON` | Enable an add-on; repeatable. |
| `--dry-run` | Resolve and plan without writing the project. |
| `--no-install` | Generate the project but defer `composer install`. |
| `--format=text|json` | Select human-readable or JSON output. |
| `--force` | Declared CLI option for forced generation. Current target safety checks still reject non-empty targets unless force is propagated by the command implementation. |
| `-n, --no-interaction` | Do not ask wizard questions. |

Inspect the installed command for the authoritative option list:

```bash
php bin/mageos new --help
```

## Composition concepts

### Profiles

Profiles provide starting selections such as `mageos-minimal`, `mageos-lite`, `mageos-full`, and `mageos-headless`.

### Profile groups

Groups model mutually exclusive choices. Current definitions include groups for theme, checkout, and search. An option can enable, disable, or force sets, layers, and add-ons.

### Sets

Sets group related Mage-OS packages, such as contact, reviews, payment, language, or sample data. In subtractive mode, disabled sets become Composer `replace` entries. In additive mode, enabled sets become Composer `require` entries.

### Layers

Layers represent cross-cutting capabilities such as GraphQL, Web API, message queue, and Hyvä compatibility. Stock layers are already present in a full project and are meaningful as explicit requirements mainly in additive mode.

### Add-ons

Add-ons represent optional external or third-party packages. They can contribute Composer packages, repositories, notes, and credential requirements.

## Composition modes

Subtractive mode starts from `mage-os/project-community-edition` and removes selected packages with Composer `replace` entries.

Additive mode starts from `mage-os/project-minimal-edition` and adds selected packages to `require`. The planner also adds `laminas/laminas-view` to additive projects.

The resolver re-applies mandatory dependencies and removes conflicting disable requests when a selected feature requires them.

## Output statuses

The text and JSON output can report:

- `planned`: a dry-run plan was produced;
- `installed`: the project was generated and Composer dependencies installed;
- `deferred`: the project was generated and installation was skipped;
- `failed`: generation or dependency installation failed.

Successful results exit with code `0`; failures exit non-zero. Interactive cancellation uses exit code `3`.

## Configuration

The application reads these environment variables:

| Variable | Default | Purpose |
| --- | --- | --- |
| `MAGEOS_CATALOG_URL` | `https://repo.mage-os.org/packages.json` | Remote catalog source. |
| `MAGEOS_REPOSITORY_URL` | `https://repo.mage-os.org/` | Composer repository used for project bootstrap. |
| `MAGEOS_INSTALLER_SKIP=1` | unset | Skips the post-create-project wizard hook. |

The local cache is stored under `var/cache/mageos`.

## Catalog definition shape

Definitions use YAML and normally contain:

```yaml
name: example
label: Example feature
description: Human-readable explanation
packages:
  - vendor/package
```

Sets and layers may add `requires`. Add-ons may add `repositories` and `notes`. Profiles contain a `selection` block. Profile-group options may contain `enables`, `disables`, `forces`, and `requires`.

## Generated project

The generated directory is a standalone Mage-OS project. Its Composer configuration is assembled from a release template, the selected base edition, enabled packages and repositories, and replacement entries. The installer source and its runtime dependencies are not required by the generated application.
