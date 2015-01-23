<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadLineStringData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity;
use Brick\Geo\LineString;

/**
 * Integrations tests for class LineStringType.
 */
class LineStringTypeTest extends TypeFunctionalTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadLineStringData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository(LineStringEntity::class);

        /** @var LineStringEntity $lineStringEntity */
        $lineStringEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($lineStringEntity);

        $lineString = $lineStringEntity->getLineString();
        $this->assertInstanceOf(LineString::class, $lineString);
        $this->assertEquals(3, $lineString->numPoints());

        $point1 = $lineString->pointN(1);
        $this->assertInstanceOf(Point::class, $point1);
        $this->assertEquals(0, $point1->x());
        $this->assertEquals(0, $point1->y());

        $point2 = $lineString->pointN(2);
        $this->assertInstanceOf(Point::class, $point2);
        $this->assertEquals(1, $point2->x());
        $this->assertEquals(0, $point2->y());

        $point3 = $lineString->pointN(3);
        $this->assertInstanceOf(Point::class, $point3);
        $this->assertEquals(1, $point3->x());
        $this->assertEquals(1, $point3->y());
    }
}
