# CakePHP 2 Acl Extras

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/acl_extras?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/acl_extras?label=Packagist)](https://packagist.org/packages/pieceofcake2/acl_extras)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/acl_extras/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/acl_extras/actions/workflows/CI.yml)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/acl_extras/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/acl_extras/CI.yml?label=CI)](https://github.com/pieceofcake2/acl_extras/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/acl_extras?label=Coverage)](https://codecov.io/gh/pieceofcake2/acl_extras)

__This is forked for CakePHP2.__

Acl Extras provides a console app that helps you manage DbAcl records more easily. Its main feature and purpose is to make generating Aco nodes for all your controllers and actions easier. It also includes some helper methods for verifying and recovering corrupted trees.

## Requirements

* PHP 8.0+
* CakePHP 2.12+ ([pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp))

## Installation

```bash
composer require pieceofcake2/acl_extras
```

This package has the type `cakephp-plugin`, so [composer/installers](https://github.com/composer/installers) installs it into the `Plugin/AclExtras` directory by default.
If your application uses the `plugins/` directory (like [pieceofcake2/app](https://github.com/pieceofcake2/app)), add the `installer-paths` setting to your `composer.json`:

```json
{
    "extra": {
        "installer-paths": {
            "plugins/{$name}/": ["type:cakephp-plugin"]
        }
    }
}
```

Then load the plugin in your `bootstrap.php`:

```php
CakePlugin::load('AclExtras');
```

## Usage

The shell requires DbAcl to be configured (`Configure::write('Acl.classname', 'DbAcl');`) and the ACL tables to exist (`bin/cake schema create DbAcl`).

You can find a list of commands by running:

```bash
bin/cake AclExtras.AclExtras -h
```

(Use `app/Console/cake` instead of `bin/cake` if your application has the traditional directory structure.)

### Commands

```bash
# Add new ACOs for new controllers and actions. Does not remove nodes from the ACO table.
bin/cake AclExtras.AclExtras aco_update

# Perform a full sync on the ACO table. Also removes orphaned ACOs that no longer have a matching controller / action.
bin/cake AclExtras.AclExtras aco_sync

# Update or sync only one plugin
bin/cake AclExtras.AclExtras aco_sync --plugin MyPlugin

# Verify or recover the tree structure of the Aco or Aro tree
bin/cake AclExtras.AclExtras verify aco
bin/cake AclExtras.AclExtras recover aro
```

The shell exits with an error code when the command fails (e.g. an unknown plugin or an invalid tree).

### Setting up the controller

You'll need to configure AuthComponent to use the Actions authorization method.
In your `AppController` add the following:

```php
public $components = [
    'Auth' => [
        'authorize' => [
            'Actions' => ['actionPath' => 'controllers/'],
        ],
    ],
];
```

## Testing

```bash
composer install
cp vendor/pieceofcake2/cakephp/tests/config/database.php vendor/pieceofcake2/app/config/
DB=mysql vendor/bin/phpunit   # or DB=pgsql / DB=sqlite
vendor/bin/phpcs
vendor/bin/phpstan analyse
```

## Issues

If you find an issue in the code or want to suggest something, please use [GitHub Issues](https://github.com/pieceofcake2/acl_extras/issues).

## License

Acl Extras is licensed under the [MIT license](LICENSE).

## Copyright

Acl Extras was originally created by [Mark Story](https://github.com/markstory/acl_extras).
