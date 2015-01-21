<?php

namespace Brick\Geo\Tests\Doctrine\Types;

use Brick\Geo\Tests\Doctrine\DataFixtures\LoadMultiLineStringData;
use Brick\Geo\Tests\Doctrine\TypeFunctionalTestCase;

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
        $repository = $this->getEntityManager()->getRepository('Brick\Geo\Tests\Doctrine\Fixtures\MultiLineStringEntity');
        $multiLineStringEntity = $repository->findOneBy(array('id' => 1));
        $this->assertNotNull($multiLineStringEntity);

        $multiLineString = $multiLineStringEntity->getMultiLineString();
        $this->assertInstanceOf('Brick\Geo\MultiLineString', $multiLineString);
    }
}
