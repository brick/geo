<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LinearRing;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPolygonData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity;
use Brick\Geo\MultiPolygon;

/**
 * Integrations tests for class MultiPolygonType.
 */
class MultiPolygonTypeTest extends TypeFunctionalTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadMultiPolygonData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(MultiPolygonEntity::class);

        /** @var MultiPolygonEntity $multiPolygonEntity */
        $multiPolygonEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiPolygonEntity);

        $multiPolygon = $multiPolygonEntity->getMultiPolygon();
        $this->assertInstanceOf(MultiPolygon::class, $multiPolygon);
        $this->assertEquals(2, $multiPolygon->numGeometries());

        /** @var Polygon $polygon1 */
        $polygon1 = $multiPolygon->geometryN(1);
        $this->assertInstanceOf(Polygon::class, $polygon1);
        $this->assertEquals(1, $polygon1->count());
        $this->assertInstanceOf(LinearRing::class, $polygon1->exteriorRing());

        $ring = $polygon1->exteriorRing();
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

        /** @var Polygon $polygon2 */
        $polygon2 = $multiPolygon->geometryN(2);
        $this->assertInstanceOf(Polygon::class, $polygon2);
        $this->assertEquals(1, $polygon2->count());
        $this->assertInstanceOf(LinearRing::class, $polygon2->exteriorRing());

        $ring = $polygon2->exteriorRing();
        $this->assertEquals(5, $ring->numPoints());

        $point1 = $ring->pointN(1);
        $this->assertInstanceOf(Point::class, $point1);
        $this->assertEquals(2, $point1->x());
        $this->assertEquals(2, $point1->y());

        $point2 = $ring->pointN(2);
        $this->assertInstanceOf(Point::class, $point2);
        $this->assertEquals(3, $point2->x());
        $this->assertEquals(2, $point2->y());

        $point3 = $ring->pointN(3);
        $this->assertInstanceOf(Point::class, $point3);
        $this->assertEquals(3, $point3->x());
        $this->assertEquals(3, $point3->y());

        $point4 = $ring->pointN(4);
        $this->assertInstanceOf(Point::class, $point4);
        $this->assertEquals(2, $point4->x());
        $this->assertEquals(3, $point4->y());

        $point5 = $ring->pointN(5);
        $this->assertInstanceOf(Point::class, $point5);
        $this->assertEquals(2, $point5->x());
        $this->assertEquals(2, $point5->y());
    }
}
