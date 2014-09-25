<?php

namespace Brick\Geo\Tests\Proxy;

use Brick\Geo\Point;
use Brick\Geo\Proxy\PointProxy;
use Brick\Geo\Tests\AbstractTestCase;

/**
 * Unit tests for class PointProxy.
 */
class PointProxyTest extends AbstractTestCase
{
    public function testProxy()
    {
        $pointProxy = new PointProxy('POINT(1 2)', false);

        $this->assertInstanceOf(Point::class, $pointProxy);
        $this->assertFalse($pointProxy->isLoaded());

        $this->assertPointEquals([1, 2], false, false, $pointProxy);
        $this->assertTrue($pointProxy->isLoaded());

        $this->assertInstanceOf(Point::class, $pointProxy->getGeometry());
    }
}
