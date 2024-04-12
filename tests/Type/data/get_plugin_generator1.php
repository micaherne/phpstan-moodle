<?php

namespace PhpstanMoodle\Test\Type;

use testing_data_generator;

use function PHPStan\Testing\assertType;

$t = new testing_data_generator();
$generator1 = $t->get_plugin_generator('mod_label');
assertType('\mod_label_generator', $generator1);