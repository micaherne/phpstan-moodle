<?php

namespace PhpstanMoodle;

use Exception;
use InvalidArgumentException;
use PhpstanMoodle\Moodle\CoreComponentBridge;
use ReflectionException;

class MoodleRootManager
{
    public function __construct(private string $moodleRoot)
    {
        if (!is_dir($this->moodleRoot) || !file_exists($this->moodleRoot . '/lib/components.json')) {
            throw new InvalidArgumentException("Moodle root does not exist or is not a valid Moodle codebase");
        }
        if (!is_dir($this->moodleRoot . '/vendor')) {
            throw new Exception("Moodle must have vendor directory - please run composer install");
        }
    }

    /**
     * @throws ReflectionException
     */
    public function initialise(): void
    {
        CoreComponentBridge::loadCoreComponent($this->moodleRoot);
        CoreComponentBridge::registerClassloader();
        CoreComponentBridge::loadStandardLibraries();
        CoreComponentBridge::fixClassloader();
        CoreComponentBridge::addMissingClassAliasDeclarations();
    }

}