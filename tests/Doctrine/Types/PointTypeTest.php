<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadPointData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\PointEntity;

/**
 * Integrations tests for class PointType.
 */
class PointTypeTest extends FunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new LoadPointData());
        $this->loadFixtures();
    }

    /**
     * @return void
     */
    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(PointEntity::class);

        /** @var PointEntity $pointEntity */
        $pointEntity = $repository->findOneBy([]);
        self::assertNotNull($pointEntity);

        $point = $pointEntity->getPoint();
        $this->assertPointEquals($point, 0.0, 0.0);
    }
}
