<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(__DIR__ . '/vendor/brick/coding-standard/ecs.php');

    $libRootPath = realpath(__DIR__ . '/../../');

    $ecsConfig->paths(
        [
            $libRootPath . '/src',
            $libRootPath . '/tests',
            $libRootPath . '/phpunit-bootstrap.php',
            $libRootPath . '/proxy-generate.php',
            __FILE__,
        ],
    );

    $ecsConfig->skip([
        $libRootPath . '/src/Proxy',

        // We want to keep LineString|CircularString order
        OrderedTypesFixer::class => null,
        PhpdocTypesOrderFixer::class => null,

        // CompoundCurve uses loose comparison intentionally when comparing points
        StrictComparisonFixer::class => $libRootPath . '/src/CompoundCurve.php',

        // AbstractWktReader uses WKT syntax in summaries, which should not be considered a sentence
        PhpdocSummaryFixer::class => $libRootPath . '/src/Io/Internal/AbstractWktReader.php',

        // GeoJsonReaderTest uses assertEquals() intentionally when comparing feature properties
        PhpUnitStrictFixer::class => $libRootPath . '/tests/IO/GeoJsonReaderTest.php',
    ]);
};
