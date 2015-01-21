<?php

namespace Brick\Geo\Tests\Doctrine\Types;

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
        $lineStringEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($lineStringEntity);

        $lineString = $lineStringEntity->getLineString();
        $this->assertInstanceOf(LineString::class, $lineString);
    }
}
