<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths(['src', 'tests'])
    ->withSkipPath('tests/data/*')
    ->withSkipPath('tests/*/data/*')
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, strictBooleans: true, typeDeclarations: true);