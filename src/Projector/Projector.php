<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;

interface Projector
{
    /**
     * Projects the given Point onto the target coordinate system.
     */
    public function project(Point $point): Point;

    /**
     * Returns the target coordinate system given the source coordinate system.
     * This method is necessary to support projection of empty geometries.
     */
    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem;
}
