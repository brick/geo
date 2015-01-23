<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LinearRing;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadPolygonData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\PolygonEntity;
use Brick\Geo\Polygon;

/**
 * Integrations tests for class PolygonType.
 */
class PolygonTypeTest extends TypeFunctionalTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadPolygonData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(PolygonEntity::class);

        /** @var PolygonEntity $polygonEntity */
        $polygonEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($polygonEntity);

        $polygon = $polygonEntity->getPolygon();
        $this->assertInstanceOf(Polygon::class, $polygon);
        $this->assertEquals(1, $polygon->count());
        $this->assertInstanceOf(LinearRing::class, $polygon->exteriorRing());

        $ring = $polygon->exteriorRing();
        $this->assertEquals(5, $ring->numPoints());

        $point1 = $ring->pointN(1);
        $this->assertInstanceOf(Point::class, $point1);
        $this->assertEquals(0, $point1->x());
        $this->assertEquals(0, $point1->y());

        $point2 = $ring->pointN(2);
        $this->assertInstanceOf(Point::class, $point2);
        $this->assertEquals(1, $point2->x());
        $this->assertEquals(0, $point2->y());

        $point3 = $ring->pointN(3);
        $this->assertInstanceOf(Point::class, $point3);
        $this->assertEquals(1, $point3->x());
        $this->assertEquals(1, $point3->y());

        $point4 = $ring->pointN(4);
        $this->assertInstanceOf(Point::class, $point4);
        $this->assertEquals(0, $point4->x());
        $this->assertEquals(1, $point4->y());

        $point5 = $ring->pointN(5);
        $this->assertInstanceOf(Point::class, $point5);
        $this->assertEquals(0, $point5->x());
        $this->assertEquals(0, $point5->y());
    }
}
