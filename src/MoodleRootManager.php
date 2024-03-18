<?php

namespace MoodlePhpstan;

use Exception;
use MoodleAnalysis\Component\CoreComponentBridge;

class MoodleRootManager
{
    public function __construct(private string $moodleRoot, private string $moodleVersion) {}

    public function createAliases(): void
    {
        CoreComponentBridge::loadCoreComponent($this->moodleRoot);
        CoreComponentBridge::registerClassloader();
        CoreComponentBridge::loadStandardLibraries();
        require_once __DIR__ . '/../resources/bootstrap-class-aliases/' . $this->moodleVersion . '.php';
        CoreComponentBridge::unregisterClassloader();
    }
}