<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Point;
use Brick\Geo\MultiPoint;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPointData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPointEntity;

/**
 * Integrations tests for class MultiPointType.
 */
class MultiPointTypeTest extends FunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadMultiPointData());
        $this->loadFixtures();
    }

    /**
     * @return void
     */
    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(MultiPointEntity::class);

        /** @var MultiPointEntity $multiPointEntity */
        $multiPointEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($multiPointEntity);

        $multiPoint = $multiPointEntity->getMultiPoint();
        $this->assertInstanceOf(MultiPoint::class, $multiPoint);
        $this->assertSame(3, $multiPoint->numGeometries());

        /** @var Point $point */
        $point = $multiPoint->geometryN(1);
        $this->assertPointEquals($point, 0.0, 0.0);

        /** @var Point $point */
        $point = $multiPoint->geometryN(2);
        $this->assertPointEquals($point, 1.0, 0.0);

        /** @var Point $point */
        $point = $multiPoint->geometryN(3);
        $this->assertPointEquals($point, 1.0, 1.0);
    }
}
