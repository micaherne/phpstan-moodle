<?php

namespace PhpstanMoodle\Test\Type;

use PHPStan\Testing\TypeInferenceTestCase;
use PhpstanMoodle\Type\EnrolGetPluginDynamicReturnTypeExtension;
use PhpstanMoodle\Type\GetAuthPluginDynamicReturnTypeExtension;
use PhpstanMoodle\Type\GetPluginGeneratorDynamicReturnTypeExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EnrolGetPluginDynamicReturnTypeExtension::class)]
#[CoversClass(GetAuthPluginDynamicReturnTypeExtension::class)]
#[CoversClass(GetPluginGeneratorDynamicReturnTypeExtension::class)]
class TypeExtensionsTest extends TypeInferenceTestCase
{

    /**
     * @return iterable<string, mixed[]>
     */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/data/enrol_get_plugin1.php');
        yield from self::gatherAssertTypes(__DIR__ . '/data/get_auth_plugin1.php');
        yield from self::gatherAssertTypes(__DIR__ . '/data/get_plugin_generator1.php');
    }

    #[DataProvider('dataFileAsserts')]
    public function testFileAsserts(
        string $assertType,
        string $file,
        mixed ...$args
    ): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/data/test.neon'
        ];
    }

}
