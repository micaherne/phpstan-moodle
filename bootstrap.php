<?php


use PhpstanMoodle\MoodleRootManager;

/** @var PHPStan\DependencyInjection\Container $container */
/** @var MoodleRootManager $moodleManager */
$moodleManager = $container->getService('moodleRootManager');
$moodleManager->initialise();