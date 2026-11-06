# Repository Guidelines

## Project Structure

- `src/` contains the PSR-4 `MageOsInstaller\` application, domain, infrastructure, and console presentation code.
- `bin/mageos` is the local CLI entry point; `config/` contains application configuration.
- `resources/definitions/` stores profiles, sets, layers, and add-on catalog YAML; `resources/catalog/` stores release/template data.
- `tests/Unit`, `tests/Contract`, and `tests/Integration` contain focused, CLI-contract, and project-generation coverage respectively.
- `docs/` and `specs/` hold research and feature artifacts. Generated projects belong under `tests/.generated/` and must remain untracked.

## Build, Test, and Development Commands

Install dependencies with `composer install`. Run the CLI locally with `php bin/mageos new <directory>` or inspect commands using `php bin/mageos list`.

Use `vendor/bin/pest` for the full test suite, or pass a specific test path for fast feedback. Before submitting changes, run `composer ci:check`; it combines linting, Rector analysis, Composer normalization, and tests. Individual checks are available as `composer lint`, `composer rector`, `composer normalize`, and `composer validate --strict`. Append `:fix` only when intentionally applying formatter, Rector, or normalization changes.

## Coding Style and Naming

Write PHP with strict types, PSR-4 namespaces, four-space indentation, and the project’s PER-CS rules. Use `PascalCase` for classes, `camelCase` for methods and variables, and descriptive `StudlyCaps` test names. Run `composer lint` before committing PHP changes. Preserve the established YAML shape and lowercase kebab-case filenames in catalog definitions.

## Testing Guidelines

Pest is the test framework. Name tests after observable behavior and place them in the matching `Unit`, `Contract`, or `Integration` area. Catalog or planning changes should cover dependency rules and generated Composer output; run the relevant integration matrix when generated structure changes. Never commit generated `vendor/` trees or credentials.

## Commits and Pull Requests

This repository has no Git commit history yet; use focused, imperative commit subjects (for example, `Add Hyva profile validation`) and keep unrelated changes separate. Pull requests should explain the user problem, affected wizard choices or generated files, tests run, and any catalog or reference changes. Keep documentation synchronized with CLI behavior and exclude secrets and large generated directories.

## Security and Configuration

Some add-ons require private Composer credentials. Keep credentials outside the repository and report vulnerabilities privately to maintainers rather than in public issues.
