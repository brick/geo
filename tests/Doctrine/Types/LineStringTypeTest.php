<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\LineString;
use Brick\Geo\Tests\Doctrine\DataFixtures\LoadLineStringData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;
use Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity;

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
        $lineStringEntity = $repository->findOneBy(['id' => 1]);
        $this->assertNotNull($lineStringEntity);

        $lineString = $lineStringEntity->getLineString();
        $this->assertInstanceOf(LineString::class, $lineString);
        $this->assertSame(3, $lineString->numPoints());

        $this->assertPointEquals($lineString->pointN(1), 0.0, 0.0);
        $this->assertPointEquals($lineString->pointN(2), 1.0, 0.0);
        $this->assertPointEquals($lineString->pointN(3), 1.0, 1.0);
    }
}
