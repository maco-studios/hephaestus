# Contributor development guide

This guide is the recommended development-start process for changing Hephaestus itself.

## 1. Prepare the repository

Use PHP 8.2 or newer and Composer 2.x:

```bash
composer install
php bin/hephaestus list
```

The second command confirms that the local CLI boots.

## 2. Understand the change boundary

Before editing, decide whether the change belongs to:

- `resources/definitions/` for catalog knowledge;
- `resources/catalog/` for release or Composer templates;
- `src/Domain/` for selection, planning, and installation rules;
- `src/Application/` for orchestration and wizard behavior;
- `src/Presentation/` for CLI input and output;
- `src/Infrastructure/` for filesystem, Composer, and process integration.

Keep generated projects under `tests/.generated/`; they are ignored and must not be committed.

## 3. Make the smallest useful change

For catalog changes, preserve lowercase kebab-case filenames and the existing YAML shape. For PHP changes, use strict types, PSR-4 namespaces, four-space indentation, and the project's PER-CS rules.

## 4. Add behavior-focused tests

Use the matching test area:

- `tests/Unit` for resolver, planner, and small domain behavior;
- `tests/Contract` for CLI and output contracts;
- `tests/Integration` for Composer and generated-project behavior.

Name tests after observable behavior. Changes to generated Composer output should include integration coverage when practical.

## 5. Run fast checks, then the full check

```bash
vendor/bin/pest
composer lint
composer rector
composer normalize
composer ci:check
```

`composer ci:check` combines linting, Rector analysis, Composer normalization, and tests. Use `:fix` commands only when you intend to modify formatting or normalization.

## 6. Verify the user workflow

For CLI changes, run:

```bash
php bin/hephaestus new --help
php bin/hephaestus new ../tmp-mageos-project --no-interaction --dry-run --format=json
```

For generation changes, run the relevant integration matrix and inspect the generated Composer output. Remove any manually created generated directory when it is no longer needed.

## 7. Keep documentation synchronized

Update the relevant tutorial, how-to, reference, or explanation whenever a command, option, catalog field, status, or development workflow changes. The README should remain a concise entry point and link to the detailed documentation here.

## Pull request checklist

- Explain the user problem and affected wizard choices.
- Describe generated files or catalog changes.
- Include tests and checks that were run.
- Mention credential or private-repository requirements without exposing secrets.
- Keep unrelated changes separate.
