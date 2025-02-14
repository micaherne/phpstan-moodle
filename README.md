phpstan-moodle
====

This is a plugin for PHPStan that enables analysis of Moodle code. 

It currently only supports bootstrapping the classloader - there are no rules as yet.

Note that this will execute code in your Moodle codebase.

## Installation

To use this plugin, install it with phpstan extension-installer:


Otherwise, require it in [Composer](https://getcomposer.org/):

```bash

composer require --dev micaherne/phpstan-moodle

```

Then create a `phpstan.neon` file in the root of your project with the following contents:

```neon

includes:
    - vendor/micaherne/phpstan-moodle/extension.neon

```

## Usage

Add the following to your `phpstan.neon` file:

```neon
parameters:
    moodle:
        rootDirectory: /path/to/moodle

```

The rootDirectory parameter *must* be an absolute path. Also, `composer install` must have been run in the Moodle root directory to create the vendor directory.

## Technical details
### Why this plugin exists
PHPStan has excellent functionality for scanning code for classes and other symbols but this is not sufficient for use with Moodle. This is due to [PHPStan's handling of class aliases](https://phpstan.org/user-guide/discovering-symbols#class-aliases), which are heavily used by Moodle. For PHPStan to be aware of class aliases these must actually exist at runtime, which means that the aliased class must also be loadable. This plugin bootstraps the Moodle classloader and sets up (most of) the aliases in the codebase so that PHPStan can understand them.

The other reason for the plugin is to enable refactoring using Rector, which delegates its symbol discovery to PHPStan. Rector assumes that all classes can be correctly discovered (and in some cases will refactor wrongly if it is unaware of a base class) so this is necessary.

### How it works
Setting the moodle.rootDirectory parameter in your PHPStan config sets up a directory scan of the whole Moodle codebase. It also calls a bootstrap script which loads the Moodle classloader and includes most of the files in the codebase that contain explicit class alias calls. Aliasing of renamed classes in db/renamedclasses.php files is handled by the classloader itself.

It also:

* sets moodle_exception as unchecked
* adds some ignores for the standard globals possibly not being defined (as we know they are)
* loads some type specifying extensions for functions like enrol_get_plugin() and get_auth_plugin() where the return type is dynamic based on the parameters in the call
