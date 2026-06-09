<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = get_auth_plugin("db");

assertType('\auth_plugin_db', $x);

$name = 'manual';
assertType('\auth_plugin_manual', get_auth_plugin($name));

$union = rand(0, 1) === 0 ? 'db' : 'manual';
assertType('\auth_plugin_manual|\auth_plugin_db', get_auth_plugin($union));
