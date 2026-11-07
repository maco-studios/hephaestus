# Explanation: how project composition works

## The installer is a project factory

Hephaestus does not generate Magento modules. It selects and composes an existing Mage-OS project distribution, then writes a Composer configuration that reflects the choices. The result is a normal Mage-OS application that can be developed and deployed independently.

The main flow is:

```text
CLI input
  -> ProjectSelection
  -> SelectionResolver
  -> ProjectPlanner
  -> ProjectWriter
  -> Composer installation
```

The wizard is only one input surface. The same domain model can be driven by command-line options and automation.

## Resolution happens before writing

The resolver merges defaults, profile values, explicit options, and profile-group effects. It then computes mandatory dependencies until the selection reaches a stable state.

This matters because a user-facing choice can imply technical requirements. For example, a theme option can enable an add-on and force layers. The generated project should remain internally consistent even when the user did not manually select every dependency.

Validation and planning happen before the target is written. Use `--dry-run` when you want to inspect this stage without filesystem or Composer changes.

## Additive and subtractive strategies

The catalog supports two useful starting philosophies:

- Additive: begin with the minimal bootable Mage-OS edition and explicitly add capabilities. This keeps the generated `require` list focused.
- Subtractive: begin with the community edition and remove selected, removable capabilities with Composer `replace`. This is convenient when a broad stock feature set is a better starting point.

Neither mode is universally better. Use additive for tightly controlled compositions and subtractive when compatibility with a broad Mage-OS baseline is more important.

## Why the output contains `require` and `replace`

Composer packages needed by the selected composition are added to `require`. In subtractive mode, packages belonging to disabled sets or layers are placed in `replace` when the catalog permits replacement. This tells Composer that the project intentionally does not provide those packages.

The planner also preserves the release template's project metadata and development dependencies. It does not invent a new Magento application layout; the bootstrap project supplies that structure.

## Catalog data and code responsibilities

Catalog YAML describes project knowledge: packages, labels, groups, dependencies, and effects. PHP code handles the generic mechanics: merging selections, resolving dependencies, validating input, planning Composer changes, and writing the project.

Prefer catalog changes for new product knowledge. Change PHP when the behavior is a general rule that cannot be represented declaratively.

## Development lifecycle

There are two projects after generation:

1. The installer repository, where catalog and composition behavior are developed.
2. The generated Mage-OS application, where storefront and application features are developed.

Keep their dependency changes and release decisions separate. The generated project is an output of the installer, not a child application that must load the installer at runtime.

## Security boundary

Some add-ons require private Composer credentials. The installer can add repository definitions, but credentials belong in Composer's external authentication configuration or another secret-management system. Do not commit tokens, `auth.json`, or generated private package caches.
