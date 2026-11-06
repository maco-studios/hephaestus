# How-to guides

## Create a project non-interactively

Supply the choices as options and disable prompts:

```bash
php bin/mageos new . \
  --no-interaction \
  --name=acme/my-mageos-project \
  --profile=mageos-minimal \
  --profile-group=theme:luma \
  --no-install
```

The project name must use Composer's `vendor/project-name` format. When the target is `.`, the project-name segment is used as the output folder and the full value becomes the generated Composer package name. An explicit target path remains the exact output directory.

Repeat array options such as `--profile-group`, `--disable-set`, and `--enable-addon` for multiple values.

## Preview a composition

Use `--dry-run` to resolve and plan the project without creating the target:

```bash
php bin/mageos new ../preview \
  --profile=mageos-full \
  --dry-run \
  --format=json
```

Interactive runs show a review before confirmation. JSON output is better for CI or scripts.

## Select a release

Pass a release supported by the catalog:

```bash
php bin/mageos new ../mageos-4 \
  --release=4.0.0 \
  --no-interaction \
  --no-install
```

The current catalog includes `latest`, `4.0.0`, and `3.5.0`. Check the repository catalog before pinning a release in automation.

## Choose a profile and groups

Profiles establish a starting composition. Profile groups select mutually exclusive choices such as theme, checkout, and search:

```bash
php bin/mageos new ../headless \
  --profile=mageos-headless \
  --profile-group=theme:hyva \
  --no-install
```

The syntax is `group:option`. An unavailable or incompatible option falls back to the catalog default during resolution.

## Enable add-ons and disable sets

Add an optional package family or remove a set from a subtractive composition:

```bash
php bin/mageos new ../custom \
  --profile=mageos-full \
  --enable-addon=yireo-emailtester \
  --disable-set=sample-data \
  --no-install
```

Add-ons may add Composer repositories or follow-up instructions. Read the final handoff output and configure credentials outside the repository.

## Defer dependency installation

Generate the project and install later:

```bash
php bin/mageos new ../my-project --no-install
cd ../my-project
composer install
```

This is useful when you want to review or edit the generated Composer configuration first.

## Consume JSON output

Use JSON output for automation:

```bash
php bin/mageos new ../my-project \
  --no-interaction \
  --dry-run \
  --format=json > plan.json
```

The payload includes `status`, `target`, `selection`, `plan`, `steps`, and `errors` when available. Treat a non-zero process exit code as failure.

## Add a catalog feature

1. Decide whether the feature is a set, layer, profile, profile-group option, or add-on.
2. Add a lowercase kebab-case YAML definition under the matching `resources/definitions/` directory.
3. Use package names exactly as Composer expects them.
4. Add dependencies through `requires`, or profile-group effects through `enables`, `disables`, and `forces`.
5. Add unit coverage for resolution and planning behavior.
6. Run the generated-project integration matrix when the Composer output changes.

Use existing definitions as templates. Keep catalog data declarative; put selection behavior in the domain resolver only when the rule cannot be expressed in catalog data.

## Troubleshoot a failed generation

Start with the error and status:

```bash
php bin/mageos new ../my-project --format=json
```

Common causes are an unsupported release, unknown profile or catalog key, an existing non-empty target, missing Composer credentials, or repository/network failure. Use `--dry-run` to separate selection errors from Composer and filesystem errors.
