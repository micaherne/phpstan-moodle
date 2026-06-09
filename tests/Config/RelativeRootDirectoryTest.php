<?php

namespace PhpstanMoodle\Test\Config;

use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Tests that a relative moodle.rootDirectory in a config file is resolved
 * against the directory of that config file, via the expandRelativePaths
 * declaration in extension.neon.
 */
#[CoversNothing]
class RelativeRootDirectoryTest extends PHPStanTestCase
{

    public function testRelativeRootDirectoryIsResolvedAgainstConfigFile(): void
    {
        $container = self::getContainer();

        $moodle = $container->getParameter('moodle');

        $this->assertSame(
            dirname(__DIR__) . '/data',
            $moodle['rootDirectory']
        );
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/data/relative_root.neon'
        ];
    }

}
