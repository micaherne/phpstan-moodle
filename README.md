phpstan-moodle
====

This is a plugin for PHPStan that enables analysis of Moodle code. 

It currently only supports bootstrapping the classloader - there are no rules as yet.

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

The rootDirectory parameter *must* be an absolute path.