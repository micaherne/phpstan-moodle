<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = get_auth_plugin("db");

assertType('\auth_plugin_db', $x);