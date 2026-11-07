# Hephaestus documentation

Hephaestus is a Composer project factory for Magento and Mage-OS users. It turns a project composition into a generated, standalone Mage-OS project.

## Choose a path

- New to the installer? Follow the [first-project tutorial](tutorial-create-a-project.md).
- Need one task? Use the [how-to guides](how-to.md).
- Need exact flags, catalog fields, or output details? Read the [reference](reference.md).
- Want to understand the composition model? Read the [architecture explanation](explanation.md).
- Want to work on this repository? Start with the [contributor development guide](contributing.md).

## Recommended starting process

1. Confirm PHP, Composer, network access, and any private repository credentials.
2. Create a small local project with the interactive wizard.
3. Run the same composition with `--dry-run` and JSON output when automating it.
4. Commit the generated project's `composer.json` and configuration only after reviewing the plan.
5. Keep the installer repository and the generated Mage-OS application as separate development concerns.

The generated application does not need Hephaestus at runtime.

## Audience

This documentation is for:

- Magento developers evaluating or moving to Mage-OS;
- Mage-OS developers starting a storefront, headless project, or custom distribution;
- PHP developers contributing to the installer or its catalog;
- teams automating repeatable Mage-OS project creation.

## Scope

This documentation covers the installer and generated-project composition. It does not replace the Mage-OS application installation, deployment, or operations documentation.
