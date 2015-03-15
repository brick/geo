<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LinearRing;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPolygonData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity;

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
        $multiPolygonEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($multiPolygonEntity);

        $multiPolygon = $multiPolygonEntity->getMultiPolygon();
        $this->assertInstanceOf(MultiPolygon::class, $multiPolygon);
        $this->assertSame(2, $multiPolygon->numGeometries());

        /** @var Polygon $polygon1 */
        $polygon1 = $multiPolygon->geometryN(1);
        $this->assertInstanceOf(Polygon::class, $polygon1);
        $this->assertSame(1, $polygon1->count());
        $this->assertInstanceOf(LinearRing::class, $polygon1->exteriorRing());

        $ring = $polygon1->exteriorRing();
        $this->assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($ring->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($ring->pointN(3), 1.0, 1.0);
        $this->assertPointEquals($ring->pointN(4), 0.0, 1.0);
        $this->assertPointEquals($ring->pointN(5), 0.0, 0.0);

        /** @var Polygon $polygon2 */
        $polygon2 = $multiPolygon->geometryN(2);
        $this->assertInstanceOf(Polygon::class, $polygon2);
        $this->assertSame(1, $polygon2->count());
        $this->assertInstanceOf(LinearRing::class, $polygon2->exteriorRing());

        $ring = $polygon2->exteriorRing();
        $this->assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 2.0, 2.0);
        $this->assertPointEquals($ring->pointN(2), 3.0, 2.0);
        $this->assertPointEquals($ring->pointN(3), 3.0, 3.0);
        $this->assertPointEquals($ring->pointN(4), 2.0, 3.0);
        $this->assertPointEquals($ring->pointN(5), 2.0, 2.0);
    }
}
