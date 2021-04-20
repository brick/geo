<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Point;
use Brick\Geo\Proxy\ProxyInterface;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadGeometryData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity;

/**
 * Integrations tests for class GeometryType.
 */
class GeometryTypeTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new LoadGeometryData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(GeometryEntity::class);

        /** @var GeometryEntity $geometryEntity */
        $geometryEntity = $repository->findOneBy(['id' => 1]);
        self::assertNotNull($geometryEntity);

        $geometry = $geometryEntity->getGeometry();

        self::assertInstanceOf(Point::class, $geometry);
        self::assertInstanceOf(ProxyInterface::class, $geometry);

        /** @var Point $geometry */
        $this->assertPointEquals($geometry, 1.0, 2.0);
    }
}
