<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadPointData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\PointEntity;

/**
 * Integrations tests for class PointType.
 */
class PointTypeTest extends TypeFunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadPointData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(PointEntity::class);

        /** @var PointEntity $pointEntity */
        $pointEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($pointEntity);

        $point = $pointEntity->getPoint();
        $this->assertPointEquals($point, 0.0, 0.0);
    }
}
