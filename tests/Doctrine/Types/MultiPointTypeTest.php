<?php

namespace Brick\Geo\Tests\Doctrine\Types;

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
        $multiPointEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiPointEntity);

        $multiPoint = $multiPointEntity->getMultiPoint();
        $this->assertInstanceOf(MultiPoint::class, $multiPoint);
    }
}
