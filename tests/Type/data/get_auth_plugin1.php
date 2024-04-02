<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = get_auth_plugin("db");

$a = assertType(\auth_plugin_db::class, $x);