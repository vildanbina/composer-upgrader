# Composer Upgrade All

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vildanbina/composer-upgrader.svg?style=flat-square)](https://packagist.org/packages/vildanbina/composer-upgrader)
[![Total Downloads](https://img.shields.io/packagist/dt/vildanbina/composer-upgrader.svg?style=flat-square)](https://packagist.org/packages/vildanbina/composer-upgrader)
[![License](https://img.shields.io/packagist/l/vildanbina/composer-upgrader.svg?style=flat-square)](https://packagist.org/packages/vildanbina/composer-upgrader)

A powerful and flexible Composer plugin that automates the process of upgrading all your project dependencies to their latest versions. Whether you want to upgrade everything, specific packages, or just preview changes, this tool has you covered with customizable options.

## Why Use This?
- **Save Time**: Automatically upgrade all dependencies in one command.
- **Flexibility**: Control stability, version increments, and target specific packages.
- **Safety**: Use the dry-run feature to preview changes without risking your project.

## Installation

Install the plugin globally or in your project using Composer:

### Global Installation (Recommended)
~~~bash
composer global require vildanbina/composer-upgrader
~~~

### Project-Specific Installation
~~~bash
composer require vildanbina/composer-upgrader
~~~

After installation, the `upgrade-all` command will be available in your Composer environment.

## Requirements
- PHP 7.4 or 8.0+
- Composer 2.0+

## Usage
Run the command in your project directory (where `composer.json` exists):

~~~bash
composer upgrade-all [options]
~~~

### Options
| Option                  | Description                                      | Default       |
|-------------------------|--------------------------------------------------|---------------|
| `--major`              | Include major version upgrades (e.g., 1.x to 2.x)| Disabled      |
| `--minor`              | Include minor version upgrades (e.g., 1.2 to 1.3)| Enabled       |
| `--patch`              | Include patch version upgrades (e.g., 1.2.3 to 1.2.4)| Enabled   |
| `--dry-run`            | Simulate upgrades without applying changes      | Disabled      |
| `--stability <value>`  | Set minimum stability (`stable`, `beta`, etc.)  | `stable`      |
| `--only <packages>`    | Comma-separated list of packages to upgrade     | All packages  |
| `--verbose`            | Show detailed output for debugging              | Disabled      |

**Note**: By default, if no version flags are specified, minor and patch upgrades are enabled. Use `--major`, `--minor`, and/or `--patch` to control the scope explicitly.
### Examples

#### Upgrade All Dependencies (Dry Run)
Preview upgrades to the latest stable versions:
~~~bash
composer upgrade-all --dry-run --stability stable
~~~

#### Upgrade Specific Packages
Upgrade only `laravel/framework` and `spatie/laravel-data`:
~~~bash
composer upgrade-all --only laravel/framework,spatie/laravel-data
~~~

#### Allow Beta Versions
Include beta releases in the upgrade:
~~~bash
composer upgrade-all --stability beta
~~~

#### Verbose Mode
See detailed version information:
~~~bash
composer upgrade-all --dry-run --verbose
~~~

## How It Works
1. **Scans `composer.json`**: Reads your `require` and `require-dev` sections.
2. **Fetches Latest Versions**: Queries Packagist (and other configured repositories) for the latest versions matching your stability preference.
3. **Compares Constraints**: Skips upgrades if the current constraint already satisfies the latest version.
4. **Updates Files**: Modifies `composer.json` and runs `composer update` (unless `--dry-run` is used).

## Troubleshooting

### "Package not found" Errors
- Ensure your Composer repositories are configured correctly (`composer config repositories`).
- Clear the Composer cache:
  ~~~bash
  composer clear-cache
  ~~~

### No Upgrades Detected
- Check the `--stability` setting; newer versions might be pre-release (e.g., `beta`). Try `--stability dev`.
- Run with `--verbose` to see which versions are being considered.

### Command Not Found
- If installed globally, ensure your global Composer bin directory is in your PATH:
  ~~~bash
  export PATH="$HOME/.composer/vendor/bin:$PATH"  # Linux/Mac
  ~~~
- If installed locally, run it via `vendor/bin/composer upgrade-all`.

## Contributing
We welcome contributions! Here's how to get started:

1. Fork the repository on [GitHub](https://github.com/vildanbina/composer-upgrader).
2. Clone your fork:
   ~~~bash
   git clone https://github.com/your-username/composer-upgrade.git
   ~~~
3. Install dependencies:
   ~~~bash
   composer install
   ~~~
4. Create a feature branch:
   ~~~bash
   git checkout -b my-feature
   ~~~
5. Commit your changes and push:
   ~~~bash
   git commit -m "Add my feature"
   git push origin my-feature
   ~~~
6. Open a pull request on GitHub.

### Development Tips
- Test locally by linking the package:
  ~~~json
  {
    "repositories": [
      {
        "type": "path",
        "url": "/path/to/composer-upgrade"
      }
    ],
    "require": {
      "vildanbina/composer-upgrader": "dev-main"
    }
  }
  ~~~
- Run tests (if added) with `composer test` (coming soon!).

## Credits
- **Author**: Vildan Bina ([vildanbina@gmail.com](mailto:vildanbina@gmail.com))
- **License**: MIT - see [LICENSE](LICENSE) for details.

## Links
- [Packagist](https://packagist.org/packages/vildanbina/composer-upgrader)
- [GitHub Repository](https://github.com/vildanbina/composer-upgrader)
- [Issue Tracker](https://github.com/vildanbina/composer-upgrader/issues)

## Support
Found a bug? Have a feature request? Open an issue on GitHub or email me at [vildanbina@gmail.com](mailto:vildanbina@gmail.com).
