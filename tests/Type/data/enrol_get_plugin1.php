<?php

namespace PhpstanMoodle\Test\Type;

use function PHPStan\Testing\assertType;

$x = enrol_get_plugin("db");

assertType('\enrol_db_plugin|null', $x);

$name = 'manual';
assertType('\enrol_manual_plugin|null', enrol_get_plugin($name));

$union = rand(0, 1) === 0 ? 'manual' : 'self';
assertType('\enrol_manual_plugin|\enrol_self_plugin|null', enrol_get_plugin($union));
