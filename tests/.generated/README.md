# Generated Mage-OS Projects

The matrix test generates real Mage-OS projects here. Each composition is bootstrapped with Composer and must contain the Magento application tree.

Run the matrix with:

```bash
./vendor/bin/pest tests/Integration/ProjectGeneration/GeneratedProjectMatrixTest.php
```

This downloads Mage-OS dependencies and may take several minutes. Remove the generated composition directories before rerunning it.

The `custom-hyva/` composition is included only when `MAGEOS_HYVA_PROJECT` is set and Composer authentication for `hyva-themes.repo.packagist.com` is configured.
