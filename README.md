# Composer Upgrader

[![GitHub Workflow Status (master)](https://img.shields.io/github/actions/workflow/status/vildanbina/composer-upgrader/tests.yml?label=Tests)](https://github.com/vildanbina/composer-upgrader/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader)
[![Latest Version](https://img.shields.io/packagist/v/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader)
[![License](https://img.shields.io/packagist/l/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader)

---

## Introduction

**Composer Upgrader** is a sleek and powerful Composer plugin designed to simplify dependency management. With a single command, upgrade all your project dependencies to their latest versions effortlessly. Key features include:

- **Customizable Upgrades**: Choose between major, minor, or patch-level updates.
- **Targeted Control**: Update specific packages with precision.
- **Stability Options**: Tailor upgrades to your preferred stability level.
- **Safe Previews**: Test changes before applying them.

This plugin updates your `composer.json` and lets you take the final step with `composer update`, keeping you in charge!

---

## Requirements

- **PHP**: `^8.0+`
- **Composer**: `2.x`

---

## Installation

Install the plugin via Composer:

~~~bash
composer require vildanbina/composer-upgrader
~~~

That’s it—no extra setup needed!

---

## Configuration

No configuration files required! Customize everything through command-line options for a lightweight experience.

---

## Commands

### `upgrade-all`

Upgrade your dependencies with ease. This command modifies `composer.json` and prompts you to run `composer update` to apply the changes.

**Usage:**

~~~bash
composer upgrade-all [options]
~~~

#### Options:
- **`--major`**: Upgrade to the latest major versions (e.g., `1.0.0` → `2.0.0`). Enabled by default.
- **`--minor`**: Upgrade to the latest minor versions (e.g., `1.0.0` → `1.1.0`). Enabled by default.
- **`--patch`**: Upgrade to the latest patch versions (e.g., `1.0.0` → `1.0.1`). Enabled by default.
- **`--dry-run`**: Preview upgrades without modifying files.
- **`--stability <level>`**: Set minimum stability (`stable`, `beta`, `alpha`, `dev`). Defaults to `stable`.
- **`--only <packages>`**: Upgrade specific packages (e.g., `vendor/package1,vendor/package2`).

#### Examples:

- **Patch-Only Upgrade:**
  ~~~bash
  composer upgrade-all --patch
  ~~~
  Output:
  ~~~
  Fetching latest package versions...
  Found vendor/package: ^1.0.0 -> 1.0.1
  Composer.json has been updated. Please run "composer update" to apply changes.
  ~~~

- **Preview Major Upgrades:**
  ~~~bash
  composer upgrade-all --major --dry-run
  ~~~
  Output:
  ~~~
  Fetching latest package versions...
  Found vendor/package: ^1.0.0 -> 2.0.0
  Dry run complete. No changes applied.
  ~~~

- **Specific Packages:**
  ~~~bash
  composer upgrade-all --only vendor/package1 --patch
  ~~~
  Output:
  ~~~
  Fetching latest package versions...
  Found vendor/package1: ^1.0.0 -> 1.0.1
  Composer.json has been updated. Please run "composer update" to apply changes.
  ~~~

After running, apply updates with:

~~~bash
composer update
~~~

---

## Features

- **Precision Upgrades**: Selectively target major, minor, or patch updates.
- **Package Filtering**: Focus on specific dependencies with `--only`.
- **Stability Flexibility**: Choose your preferred stability level.
- **Safe Execution**: Preview changes with `--dry-run` before committing.
- **Verbose Insights**: Use `-v` for detailed upgrade logs.

---

## Contributing

Love to improve this tool? Check out [CONTRIBUTING](.github/CONTRIBUTING.md) for how to dive in—bug fixes, features, or docs welcome!

---

## Security Vulnerabilities

Spot a security concern? Email [vildanbina@gmail.com](mailto:vildanbina@gmail.com) directly—we’ll address it promptly!

---

## Credits

- **[Vildan Bina](https://github.com/vildanbina)** – Creator & Lead Developer
- **All Contributors** – Thanks for your awesome support! ([See contributors](../../contributors))

---

## License

Licensed under the MIT License (MIT). See [License File](LICENSE.md) for details.

Upgrade smarter, not harder, with **Composer Upgrader**! 🎉