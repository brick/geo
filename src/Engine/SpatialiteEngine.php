<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Engine\Database\Internal\AbstractDatabaseWkbEngine;
use Brick\Geo\Engine\Internal\TypeChecker;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Override;

/**
 * Database engine based on the SpatiaLite extension for SQLite.
 */
final readonly class SpatialiteEngine extends AbstractDatabaseWkbEngine
{
    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction) : Point
    {
        $result = $this->queryGeometry('ST_Line_Interpolate_Point', $lineString, $fraction);
        TypeChecker::check($result, Point::class);

        return $result;
    }

    public function getSpatialiteVersion() : string
    {
        return $this->driver->executeQuery('SELECT spatialite_version()')->get(0)->asString();
    }
}
