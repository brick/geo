<?php

namespace Brick\Geo\Tests\Doctrine\Types;

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
        $polygonEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($polygonEntity);

        $polygon = $polygonEntity->getPolygon();
        $this->assertInstanceOf(Polygon::class, $polygon);
    }
}
