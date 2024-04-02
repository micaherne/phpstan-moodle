<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = enrol_get_plugin("db");

$a = assertType(\enrol_db_plugin::class, $x);