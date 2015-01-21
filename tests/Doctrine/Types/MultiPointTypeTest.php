<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPointData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;

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
        $repository = $this->getEntityManager()->getRepository('Brick\Geo\Tests\Doctrine\Fixtures\MultiPointEntity');
        $multiPointEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiPointEntity);

        $multiPoint = $multiPointEntity->getMultiPoint();
        $this->assertInstanceOf('Brick\Geo\MultiPoint', $multiPoint);
    }
}
