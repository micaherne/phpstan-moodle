<?php

namespace MoodlePhpstan;

use Exception;
use InvalidArgumentException;
use MoodleAnalysis\Component\CoreComponentBridge;

class MoodleRootManager
{
    public function __construct(private string $moodleRoot, private string $moodleVersion) {
        if (!is_dir($this->moodleRoot) || !file_exists($this->moodleRoot . '/lib/components.json')) {
            throw new InvalidArgumentException("Moodle root does not exist or is not a valid Moodle codebase");
        }
        if (!is_dir($this->moodleRoot . '/vendor')) {
            throw new Exception("Moodle must have vendor directory - please run composer install");
        }
    }

    public function createAliases(): void
    {
        CoreComponentBridge::loadCoreComponent($this->moodleRoot);
        CoreComponentBridge::registerClassloader();
        CoreComponentBridge::loadStandardLibraries();
        require_once __DIR__ . '/../resources/bootstrap-class-aliases/' . $this->moodleVersion . '.php';
        CoreComponentBridge::unregisterClassloader();
    }
}