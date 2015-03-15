<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Point;
use Brick\Geo\Proxy\GeometryProxy;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadGeometryData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity;

/**
 * Integrations tests for class GeometryType.
 */
class GeometryTypeTest extends TypeFunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadGeometryData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(GeometryEntity::class);

        /** @var GeometryEntity $geometryEntity */
        $geometryEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($geometryEntity);

        /** @var GeometryProxy $geometry */
        $geometry = $geometryEntity->getGeometry();

        /** @var Point $point */
        $point = $geometry->getGeometry();

        $this->assertPointEquals($point, 0.0, 0.0);
    }
}
