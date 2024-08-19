<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;
use Brick\Geo\Projector\RoundCoordinatesProjector;
use Brick\Geo\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class RoundCoordinatesProjector.
 */
class RoundCoordinatesProjectorTest extends AbstractTestCase
{
    #[DataProvider('providerProject')]
    public function testProject(float $x, float $y, int $srid, int $precision, float $expectedX, float $expectedY): void
    {
        $projector = new RoundCoordinatesProjector($precision);

        $point = Point::xy($x, $y, $srid);
        $projected = $projector->project($point);

        $this->assertPointXYEquals($expectedX, $expectedY, $srid, $projected);
    }

    public static function providerProject(): array
    {
        return [
            [1.234567, 2.345678, 1234, 0, 1, 2],
            [1.234567, 2.345678, 2345, 1, 1.2, 2.3],
            [1.234567, 2.345678, 3456, 2, 1.23, 2.35],
            [1.234567, 2.345678, 4567, 3, 1.235, 2.346],
        ];
    }

    #[DataProvider('providerGetTargetCoordinateSystem')]
    public function testGetTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): void
    {
        $projector = new RoundCoordinatesProjector(2);
        $targetCoordinateSystem = $projector->getTargetCoordinateSystem($sourceCoordinateSystem);

        $this->assertSame($sourceCoordinateSystem, $targetCoordinateSystem);
    }

    public static function providerGetTargetCoordinateSystem(): array
    {
        return [
            [CoordinateSystem::xy(4326)],
            [CoordinateSystem::xyz(2154)],
        ];
    }
}
