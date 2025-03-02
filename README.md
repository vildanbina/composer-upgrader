# Composer Upgrader

[![GitHub Workflow Status (main)](https://img.shields.io/github/actions/workflow/status/vildanbina/composer-upgrader/tests.yml?label=Tests)](https://github.com/vildanbina/composer-upgrader/actions) [![Total Downloads](https://img.shields.io/packagist/dt/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader) [![Latest Version](https://img.shields.io/packagist/v/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader) [![License](https://img.shields.io/packagist/l/vildanbina/composer-upgrader)](https://packagist.org/packages/vildanbina/composer-upgrader)

---

## Introduction

**Composer Upgrader** is a sleek and powerful Composer plugin designed to simplify dependency management in PHP projects. With a single command, upgrade all your dependencies to their latest versions effortlessly. Whether you're maintaining a small library or a large application, this tool offers:

- **Flexible Upgrades**: Choose major, minor, or patch-level updates.
- **Targeted Updates**: Focus on specific packages with precision.
- **Stability Control**: Set your preferred stability level for peace of mind.
- **Safe Previews**: Test changes before applying them.

It updates your `composer.json` and prompts you to run `composer update`, keeping you in full control of your project!

---

## Requirements

- **PHP**: `^8.0+` (Optimized for modern PHP versions)
- **Composer**: `2.x`

---

## 📦 Installation

You can install Composer Upgrader either **locally** in your project or **globally** on your system:

### Local Installation
Add it to your project:

~~~bash
composer require vildanbina/composer-upgrader
~~~

### Global Installation
Install it globally for use across all projects:

~~~bash
composer global require vildanbina/composer-upgrader
~~~

> **Note**: Ensure your global Composer bin directory (e.g., `~/.composer/vendor/bin` or `~/.config/composer/vendor/bin`) is in your PATH to run `composer upgrade-all` from anywhere. Check with `echo $PATH` and update if needed (e.g., `export PATH="$HOME/.composer/vendor/bin:$PATH"`).

No additional setup required—ready to use either way!

---

## Configuration

No configuration files needed! Customize your upgrade experience directly through command-line options for a lightweight, hassle-free setup.

---

## Commands

### `upgrade-all`

Upgrade your project dependencies with ease. This command scans your `composer.json`, updates it with the latest compatible versions, and advises you to run `composer update` to apply the changes.

**Usage:**

~~~bash
composer upgrade-all [options]
~~~

#### Options:
- **`--major`**: Upgrade to the latest major versions (e.g., `1.0.0` → `2.0.0`). Enabled by default.
- **`--minor`**: Upgrade to the latest minor versions (e.g., `1.0.0` → `1.1.0`). Enabled by default.
- **`--patch`**: Upgrade to the latest patch versions (e.g., `1.0.0` → `1.0.1`). Enabled by default.
- **`--dry-run`**: Preview upgrades without modifying files—ideal for testing.
- **`--stability <level>`**: Set minimum stability (`stable`, `beta`, `alpha`, `dev`). Defaults to `stable`.
- **`--only <packages>`**: Upgrade specific packages (e.g., `vendor/package1,vendor/package2`).

#### Examples:

- **Patch-Only Upgrade:**
  ~~~bash
  composer upgrade-all --patch
  ~~~
  **Output:**
  ~~~
  Fetching latest package versions...
  Found vendor/package: ^1.0.0 -> 1.0.1
  Composer.json has been updated. Please run "composer update" to apply changes.
  ~~~

- **Preview Major Upgrades:**
  ~~~bash
  composer upgrade-all --major --dry-run
  ~~~
  **Output:**
  ~~~
  Fetching latest package versions...
  Found vendor/package: ^1.0.0 -> 2.0.0
  Dry run complete. No changes applied.
  ~~~

- **Specific Packages:**
  ~~~bash
  composer upgrade-all --only vendor/package1 --patch
  ~~~
  **Output:**
  ~~~
  Fetching latest package versions...
  Found vendor/package1: ^1.0.0 -> 1.0.1
  Composer.json has been updated. Please run "composer update" to apply changes.
  ~~~

After running, finalize the updates with:

~~~bash
composer update
~~~

---

## Features

- **Precision Upgrades**: Tailor updates to major, minor, or patch levels with ease.
- **Selective Targeting**: Use `--only` to upgrade just the packages you need.
- **Stability Flexibility**: Match your project’s stability needs (`stable`, `beta`, etc.).
- **Safe Previews**: Test changes with `--dry-run` before committing.
- **Verbose Logs**: Add `-v` for detailed insights into the upgrade process.

---

## Contributing

Want to make this tool even better? Contributions are welcome! Check out our [CONTRIBUTING](.github/CONTRIBUTING.md) guide for details on submitting bug fixes, features, or documentation improvements.

---

## Security Vulnerabilities

Spot a security issue? Please email [vildanbina@gmail.com](mailto:vildanbina@gmail.com) directly instead of using the issue tracker. We’ll address it swiftly!

---

## Credits

- **[Vildan Bina](https://github.com/vildanbina)** – Creator & Lead Developer
- **All Contributors** – A huge thanks for your support! ([See contributors](../../contributors))

---

## License

Licensed under the MIT License (MIT). See the [License File](LICENSE.md) for more information.

Upgrade smarter, not harder, with **Composer Upgrader**! 🎉