<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPointData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPointEntity;
use Brick\Geo\MultiPoint;

/**
 * Integrations tests for class MultiPointType.
 */
class MultiPointTypeTest extends TypeFunctionalTestCase
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

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(MultiPointEntity::class);

        /** @var MultiPointEntity $multiPointEntity */
        $multiPointEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiPointEntity);

        $multiPoint = $multiPointEntity->getMultiPoint();
        $this->assertInstanceOf(MultiPoint::class, $multiPoint);
        $this->assertEquals(3, $multiPoint->numGeometries());

        /** @var Point $point1 */
        $point1 = $multiPoint->geometryN(1);
        $this->assertInstanceOf(Point::class, $point1);
        $this->assertEquals(0, $point1->x());
        $this->assertEquals(0, $point1->y());

        /** @var Point $point2 */
        $point2 = $multiPoint->geometryN(2);
        $this->assertInstanceOf(Point::class, $point2);
        $this->assertEquals(1, $point2->x());
        $this->assertEquals(0, $point2->y());

        /** @var Point $point3 */
        $point3 = $multiPoint->geometryN(3);
        $this->assertInstanceOf(Point::class, $point3);
        $this->assertEquals(1, $point3->x());
        $this->assertEquals(1, $point3->y());
    }
}
