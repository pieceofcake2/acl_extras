# Acl Extras Documentation

Acl Extras provides a console shell that helps you manage DbAcl records. Its main purpose
is to generate the ACO nodes for all your controllers and actions. It also verifies and
recovers corrupted ACO and ARO trees. It runs on
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).

- [Installation](#installation)
- [Requirements for the Shell](#requirements-for-the-shell)
- [Commands](#commands)
- [The ACO Tree](#the-aco-tree)
- [Setting up the Controller](#setting-up-the-controller)
- [Updating ACOs from Your Code](#updating-acos-from-your-code)
- [Credits](#credits)

## Installation

Install the plugin with [Composer](https://getcomposer.org/):

```bash
composer require pieceofcake2/acl_extras
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory
by `composer/installers`. Set `installer-paths` in your application's `composer.json` so it
is installed into the right place:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

Load the plugin in `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`).
If you already use `CakePlugin::loadAll()`, this is not necessary:

```php
CakePlugin::load('AclExtras');
```

## Requirements for the Shell

The shell requires DbAcl to be configured in `config/core.php` (legacy layout:
`app/Config/core.php`):

```php
Configure::write('Acl.classname', 'DbAcl');
```

and the ACL tables to exist:

```bash
bin/cake schema create DbAcl
```

Use `app/Console/cake` instead of `bin/cake` if your application has the legacy directory
layout.

## Commands

List the commands with:

```bash
bin/cake AclExtras.AclExtras -h
```

```bash
# Add new ACOs for new controllers and actions. Does not remove nodes from the ACO table.
bin/cake AclExtras.AclExtras aco_update

# Perform a full sync on the ACO table. Also removes orphaned ACOs that no longer have a
# matching controller or action.
bin/cake AclExtras.AclExtras aco_sync

# Update or sync only one plugin
bin/cake AclExtras.AclExtras aco_sync --plugin MyPlugin

# Verify or recover the tree structure of the ACO or ARO tree
bin/cake AclExtras.AclExtras verify aco
bin/cake AclExtras.AclExtras recover aro
```

| Command | Description |
| --- | --- |
| `aco_update` | Add ACOs for new controllers and actions. Existing nodes are kept. |
| `aco_sync` | Add ACOs for new controllers and actions, and remove the ACOs of controllers and actions that no longer exist. |
| `verify aco\|aro` | Verify the tree structure of the ACO or ARO tree. |
| `recover aco\|aro` | Recover a corrupted ACO or ARO tree. |

`aco_update` and `aco_sync` accept `--plugin` (`-p`) to process only one plugin.

The shell exits with an error code when the command fails, for example for an unknown
plugin or an invalid tree.

## The ACO Tree

`aco_update` and `aco_sync` create one ACO per public action, under a root node named
`controllers`:

```
controllers
├── Posts
│   ├── index
│   ├── view
│   └── ...
└── ContactManager          (a plugin)
    └── Contacts
        ├── index
        └── ...
```

`AppController` and `<Plugin>AppController` get no node of their own. Methods of the base
`Controller` class, methods whose name starts with `_` and the event callbacks of the
controller are skipped. Public methods that a controller inherits from your `AppController`
are added to each controller.

## Setting up the Controller

Configure `AuthComponent` to use the `Actions` authorization, so that the ACOs created by the
shell are checked for each request. In `src/Controller/AppController.php` (legacy layout:
`app/Controller/AppController.php`):

```php
class AppController extends Controller
{
    public $components = [
        'Auth' => [
            'authorize' => [
                'Actions' => ['actionPath' => 'controllers/'],
            ],
        ],
    ];
}
```

## Updating ACOs from Your Code

The shell uses the `AclExtras` class of the plugin, which you can also use in your own code,
for example in an admin controller:

```php
App::uses('AclExtras', 'AclExtras.Lib');

$aclExtras = new AclExtras();
$aclExtras->startup($this);
$aclExtras->aco_sync();
```

When the controller has the `Session` component, the messages are set as flash messages, so
the controller must also have the `Flash` component.

## Credits

Acl Extras was originally created by [Mark Story](https://github.com/markstory/acl_extras).

## Origin

This package is a maintained fork of [markstory/acl_extras](https://github.com/markstory/acl_extras)
(`markstory/acl_extras` on Packagist, last upstream version 1.2.1), originally by Mark Story. It is
maintained for CakePHP 2 on modern PHP by [pieceofcake2](https://github.com/pieceofcake2). Changes
from the original are listed in the
[commit history since the fork](https://github.com/pieceofcake2/acl_extras/compare/1174758...2.x).
