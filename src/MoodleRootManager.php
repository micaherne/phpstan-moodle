<?php

namespace MoodlePhpstan;

use Exception;

class MoodleRootManager
{
    public function __construct(private string $moodleRoot, private ?string $moodleVersion) {}

    public function fail()
    {
        throw new Exception("Something failed");
    }
}