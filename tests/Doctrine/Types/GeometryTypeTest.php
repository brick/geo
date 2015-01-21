<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadGeometryData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;

/**
 * Integrations tests for class GeometryType.
 */
class GeometryTypeTest extends TypeFunctionalTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addFixture(new LoadGeometryData());
        $this->loadFixtures();
    }

    public function testReadFromDbAndConvertToPHPValue()
    {
        $repository = $this->getEntityManager()->getRepository('Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity');
        $geometryEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($geometryEntity);

        $geometry = $geometryEntity->getGeometry();
        $this->assertInstanceOf('Brick\Geo\Geometry', $geometry);
    }
}
