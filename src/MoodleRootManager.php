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
        $this->loadOtherAliases();
    }

    /**
     * Load some other files which create class aliases.
     */
    public function loadOtherAliases(): void {
        // Global is necessary here as some of the included files
        // may use it.
        global $CFG;

        foreach (
            [
                'lib/badgeslib.php',
                'lib/classes/plugin_manager.php',
                'lib/editor/tiny/lib.php',
                'lib/phpxmlrpc/Exception/PhpXmlrpcException.php',
                'mod/assign/tests/base_test.php',
            ] as $file
        ) {
            if (file_exists($CFG->dirroot . '/' . $file)) {
                require_once $CFG->dirroot . '/' . $file;
            }
        }
    }
}