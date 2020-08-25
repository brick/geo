<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadPolygonData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\PolygonEntity;

/**
 * Integrations tests for class PolygonType.
 */
class PolygonTypeTest extends FunctionalTestCase
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

    /**
     * @return void
     */
    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(PolygonEntity::class);

        /** @var PolygonEntity $polygonEntity */
        $polygonEntity = $repository->findOneBy(['id' => 1]);
        self::assertNotNull($polygonEntity);

        $polygon = $polygonEntity->getPolygon();
        self::assertInstanceOf(Polygon::class, $polygon);
        self::assertSame(1, $polygon->count());
        self::assertInstanceOf(LineString::class, $polygon->exteriorRing());

        $ring = $polygon->exteriorRing();
        self::assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($ring->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($ring->pointN(3), 1.0, 1.0);
        $this->assertPointEquals($ring->pointN(4), 0.0, 1.0);
        $this->assertPointEquals($ring->pointN(5), 0.0, 0.0);
    }
}
