<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Point;

/**
 * Base class for Geometry tests.
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param Point      $point
     */
    final protected function assertPointEquals($x, $y, $z, $m, Point $point)
    {
        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
        $this->assertSame($m, $point->m());
    }
}
