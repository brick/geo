<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadLineStringData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;

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
        $repository = $this->getEntityManager()->getRepository('Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity');
        $lineStringEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($lineStringEntity);

        $lineString = $lineStringEntity->getLineString();
        $this->assertInstanceOf('Brick\Geo\LineString', $lineString);
    }
}
