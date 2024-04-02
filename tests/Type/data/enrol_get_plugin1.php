<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = enrol_get_plugin("db");

assertType('enrol_db_plugin|null', $x);