# Contributing to Mage-OS Installer

Thank you for contributing. Mage-OS Installer is a PHP CLI project that turns the MageOS Maker composition model into a guided Mage-OS project factory.

## Before you start

1. Check the existing issues and documentation.
2. For behavior changes, describe the user-facing wizard flow and generated-project impact.
3. Keep changes focused. Catalog changes, planner changes, renderer changes, and documentation changes should be easy to review independently.

## Local setup

```bash
git clone <repository-url>
cd mage-os-installer
composer install
```

## Tests and checks

Run the focused tests while developing, then run the complete suite before opening a pull request:

```bash
vendor/bin/pest tests/Unit/Wizard/PromptOptionLabelsTest.php
vendor/bin/pest
composer lint
composer validate --strict
```

The project-generation integration test creates real Mage-OS project trees under `tests/.generated/`. These directories are intentionally ignored because Composer dependencies are large. Do not commit generated `vendor/` directories or credentials.

## Catalog and planner changes

Definitions under `resources/definitions/` are user-facing catalog data. When changing one:

- preserve the existing YAML shape and naming conventions;
- document release, distribution, and dependency gates;
- verify mandatory dependencies and incompatible combinations;
- update or add tests for the resulting Composer plan;
- run the real project-generation matrix when the generated structure or Composer output changes.

Generated Composer files must remain valid according to the [Composer schema](https://getcomposer.org/schema.json). The generated project should contain normal Mage-OS directories and must not depend on installer-only metadata files.

## Pull requests

Pull requests should explain:

- what user problem is being solved;
- which wizard choices or generated files are affected;
- how the change was tested;
- whether catalog definitions or upstream MageOS Maker behavior were changed.

Please include tests for behavior changes and keep documentation in sync with the command-line interface. Do not include secrets, private repository credentials, or large generated project directories in commits.

## Commit and review expectations

Use clear, focused commits. Reviewers should be able to verify selection rules, Composer output, generated project structure, and terminal behavior independently. Be respectful, specific, and open to changes requested during review.

## Security issues

Do not report security vulnerabilities in public issues. Until a dedicated security contact is published, contact the repository maintainers privately with a minimal description and reproduction details.
