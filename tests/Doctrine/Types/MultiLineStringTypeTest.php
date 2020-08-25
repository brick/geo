<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiLineStringData;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiLineStringEntity;

/**
 * Integrations tests for class MultiLineStringType.
 */
class MultiLineStringTypeTest extends FunctionalTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadMultiLineStringData());
        $this->loadFixtures();
    }

    /**
     * @return void
     */
    public function testReadFromDbAndConvertToPHPValue() : void
    {
        $repository = $this->getEntityManager()->getRepository(MultiLineStringEntity::class);

        /** @var MultiLineStringEntity $multiLineStringEntity */
        $multiLineStringEntity = $repository->findOneBy(['id' => 1]);
        self::assertNotNull($multiLineStringEntity);

        $multiLineString = $multiLineStringEntity->getMultiLineString();
        self::assertInstanceOf(MultiLineString::class, $multiLineString);
        self::assertSame(2, $multiLineString->numGeometries());

        /** @var LineString $lineString1 */
        $lineString1 = $multiLineString->geometryN(1);
        self::assertInstanceOf(LineString::class, $lineString1);
        self::assertSame(3, $lineString1->numPoints());

        $this->assertPointEquals($lineString1->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($lineString1->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($lineString1->pointN(3), 1.0, 1.0);

        /** @var LineString $lineString2 */
        $lineString2 = $multiLineString->geometryN(2);
        self::assertInstanceOf(LineString::class, $lineString2);
        self::assertSame(3, $lineString2->numPoints());

        $this->assertPointEquals($lineString2->pointN(1), 2.0, 2.0);
        $this->assertPointEquals($lineString2->pointN(2), 3.0, 2.0);
        $this->assertPointEquals($lineString2->pointN(3), 3.0, 3.0);
    }
}
