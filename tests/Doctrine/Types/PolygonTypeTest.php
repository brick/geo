<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LinearRing;
use Brick\Geo\Polygon;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadPolygonData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\PolygonEntity;

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
        $polygonEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($polygonEntity);

        $polygon = $polygonEntity->getPolygon();
        $this->assertInstanceOf(Polygon::class, $polygon);
        $this->assertSame(1, $polygon->count());
        $this->assertInstanceOf(LinearRing::class, $polygon->exteriorRing());

        $ring = $polygon->exteriorRing();
        $this->assertSame(5, $ring->numPoints());

        $this->assertPointEquals($ring->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($ring->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($ring->pointN(3), 1.0, 1.0);
        $this->assertPointEquals($ring->pointN(4), 0.0, 1.0);
        $this->assertPointEquals($ring->pointN(5), 0.0, 0.0);
    }
}
