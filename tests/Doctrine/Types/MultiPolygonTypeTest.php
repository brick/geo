<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiPolygonData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;

/**
 * Integrations tests for class MultiPolygonType.
 */
class MultiPolygonTypeTest extends TypeFunctionalTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadMultiPolygonData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository('Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity');
        $multiPolygonEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiPolygonEntity);

        $multiPolygon = $multiPolygonEntity->getMultiPolygon();
        $this->assertInstanceOf('Brick\Geo\MultiPolygon', $multiPolygon);
    }
}
