<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/tests',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::NAMING,
        SetList::STRICT_BOOLEANS,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->skip([
        __DIR__.'/tests/TestCase.php',
    ]);
};
