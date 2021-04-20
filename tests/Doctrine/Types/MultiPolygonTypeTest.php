<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPolygonData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity;

/**
 * Integrations tests for class MultiPolygonType.
 */
class MultiPolygonTypeTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new LoadMultiPolygonData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(MultiPolygonEntity::class);

        /** @var MultiPolygonEntity $multiPolygonEntity */
        $multiPolygonEntity = $repository->findOneBy(['id' => 1]);
        self::assertNotNull($multiPolygonEntity);

        $multiPolygon = $multiPolygonEntity->getMultiPolygon();
        self::assertInstanceOf(MultiPolygon::class, $multiPolygon);
        self::assertSame(2, $multiPolygon->numGeometries());

        /** @var Polygon $polygon1 */
        $polygon1 = $multiPolygon->geometryN(1);
        self::assertInstanceOf(Polygon::class, $polygon1);
        self::assertSame(1, $polygon1->count());
        self::assertInstanceOf(LineString::class, $polygon1->exteriorRing());

        $ring = $polygon1->exteriorRing();
        self::assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($ring->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($ring->pointN(3), 1.0, 1.0);
        $this->assertPointEquals($ring->pointN(4), 0.0, 1.0);
        $this->assertPointEquals($ring->pointN(5), 0.0, 0.0);

        /** @var Polygon $polygon2 */
        $polygon2 = $multiPolygon->geometryN(2);
        self::assertInstanceOf(Polygon::class, $polygon2);
        self::assertSame(1, $polygon2->count());
        self::assertInstanceOf(LineString::class, $polygon2->exteriorRing());

        $ring = $polygon2->exteriorRing();
        self::assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 2.0, 2.0);
        $this->assertPointEquals($ring->pointN(2), 3.0, 2.0);
        $this->assertPointEquals($ring->pointN(3), 3.0, 3.0);
        $this->assertPointEquals($ring->pointN(4), 2.0, 3.0);
        $this->assertPointEquals($ring->pointN(5), 2.0, 2.0);
    }
}
