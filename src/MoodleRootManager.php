<?php

namespace MoodlePhpstan;

use Exception;
use MoodleAnalysis\Component\CoreComponentBridge;

class MoodleRootManager
{
    public function __construct(private string $moodleRoot, private ?string $moodleVersion) {}

    public function createAliases(): void
    {
        CoreComponentBridge::loadCoreComponent($this->moodleRoot);
        CoreComponentBridge::loadStandardLibraries();
    }
}