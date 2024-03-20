<?php declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use MoodlePhpstan\Console\Command\GenerateClassAliasBootstrap;
use Symfony\Component\Console\Application;

$app = new Application('phpstan-moodle', '0.1');
$app->addCommands([
    new GenerateClassAliasBootstrap()
]);
$app->run();

