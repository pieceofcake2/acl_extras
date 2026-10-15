# CakePHP 2 Acl Extras

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/acl_extras?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/acl_extras?label=Packagist)](https://packagist.org/packages/pieceofcake2/acl_extras)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/acl_extras/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/acl_extras/actions/workflows/CI.yml)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/acl_extras/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/acl_extras/CI.yml?label=CI)](https://github.com/pieceofcake2/acl_extras/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/acl_extras?label=Coverage)](https://codecov.io/gh/pieceofcake2/acl_extras)

Acl Extras provides a console shell that helps you manage DbAcl records: it generates the
ACO nodes for all your controllers and actions, and verifies and recovers corrupted ACO and
ARO trees. This is a fork of [markstory/acl_extras](https://github.com/markstory/acl_extras)
for [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).

## Documentation

- Website: https://pieceofcake2.github.io/acl_extras/
- Markdown: [docs/README.md](docs/README.md) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/AclExtras/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

- PHP 8.0+
- pieceofcake2/cakephp 2.12+

## Installation

```bash
composer require pieceofcake2/acl_extras
```

Then load the plugin with `CakePlugin::load('AclExtras')` and run
`bin/cake AclExtras.AclExtras aco_sync` (legacy layout: `app/Console/cake`).

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## Testing

```bash
composer install
cp vendor/pieceofcake2/cakephp/tests/config/database.php vendor/pieceofcake2/app/config/
DB=mysql vendor/bin/phpunit   # or DB=pgsql / DB=sqlite
vendor/bin/phpcs
vendor/bin/phpstan analyse
```

## License

Acl Extras is licensed under the [MIT license](LICENSE). It was originally created by
[Mark Story](https://github.com/markstory/acl_extras).
