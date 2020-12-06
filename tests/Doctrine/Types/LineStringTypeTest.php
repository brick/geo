<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadLineStringData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity;

/**
 * Integrations tests for class LineStringType.
 */
class LineStringTypeTest extends FunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new LoadLineStringData());
        $this->loadFixtures();
    }

    /**
     * @return void
     */
    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(LineStringEntity::class);

        /** @var LineStringEntity $lineStringEntity */
        $lineStringEntity = $repository->findOneBy(['id' => 1]);
        self::assertNotNull($lineStringEntity);

        $lineString = $lineStringEntity->getLineString();
        self::assertInstanceOf(LineString::class, $lineString);
        self::assertSame(3, $lineString->numPoints());

        $this->assertPointEquals($lineString->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($lineString->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($lineString->pointN(3), 1.0, 1.0);
    }
}
