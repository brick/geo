<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiLineStringData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiLineStringEntity;
use Brick\Geo\MultiLineString;

/**
 * Integrations tests for class MultiLineStringType.
 */
class MultiLineStringTypeTest extends TypeFunctionalTestCase
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

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(MultiLineStringEntity::class);

        /** @var MultiLineStringEntity $multiLineStringEntity */
        $multiLineStringEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiLineStringEntity);

        $multiLineString = $multiLineStringEntity->getMultiLineString();
        $this->assertInstanceOf(MultiLineString::class, $multiLineString);
        $this->assertEquals(2, $multiLineString->numGeometries());

        /** @var LineString $lineString1 */
        $lineString1 = $multiLineString->geometryN(1);
        $this->assertInstanceOf(LineString::class, $lineString1);
        $this->assertEquals(3, $lineString1->numPoints());

        $point1 = $lineString1->pointN(1);
        $this->assertInstanceOf(Point::class, $point1);
        $this->assertEquals(0, $point1->x());
        $this->assertEquals(0, $point1->y());

        $point2 = $lineString1->pointN(2);
        $this->assertInstanceOf(Point::class, $point2);
        $this->assertEquals(1, $point2->x());
        $this->assertEquals(0, $point2->y());

        $point3 = $lineString1->pointN(3);
        $this->assertInstanceOf(Point::class, $point3);
        $this->assertEquals(1, $point3->x());
        $this->assertEquals(1, $point3->y());

        /** @var LineString $lineString2 */
        $lineString2 = $multiLineString->geometryN(2);
        $this->assertInstanceOf(LineString::class, $lineString2);
        $this->assertEquals(3, $lineString2->numPoints());

        $point4 = $lineString2->pointN(1);
        $this->assertInstanceOf(Point::class, $point4);
        $this->assertEquals(2, $point4->x());
        $this->assertEquals(2, $point4->y());

        $point5 = $lineString2->pointN(2);
        $this->assertInstanceOf(Point::class, $point5);
        $this->assertEquals(3, $point5->x());
        $this->assertEquals(2, $point5->y());

        $point6 = $lineString2->pointN(3);
        $this->assertInstanceOf(Point::class, $point6);
        $this->assertEquals(3, $point6->x());
        $this->assertEquals(3, $point6->y());
    }
}
