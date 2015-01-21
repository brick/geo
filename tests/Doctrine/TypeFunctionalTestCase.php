<?php

namespace Brick\Geo\Tests\Doctrine;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;
use Doctrine\Tests\DbalFunctionalTestCase;

class TypeFunctionalTestCase extends DbalFunctionalTestCase
{

    /**
     * @var MockPlatform
     */
    private $platform;

    /**
     * @var Loader
     */
    private $fixtureLoader;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ORMExecutor
     */
    private $ormExecutor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (getenv('ENGINE') == 'SQLite3' || getenv('ENGINE') == 'GEOS') {
            $this->markTestSkipped('The doctrine types currently only work with MySQL and PostgreSQL');
        }

        $this->platform = $this->_conn->getDatabasePlatform();

        $this->platform->registerDoctrineTypeMapping('geometry', 'binary');
        $this->platform->registerDoctrineTypeMapping('linestring', 'binary');
        $this->platform->registerDoctrineTypeMapping('multilinestring', 'binary');
        $this->platform->registerDoctrineTypeMapping('multipoint', 'binary');
        $this->platform->registerDoctrineTypeMapping('multipolygon', 'binary');
        $this->platform->registerDoctrineTypeMapping('point', 'binary');
        $this->platform->registerDoctrineTypeMapping('polygon', 'binary');

        switch ($this->platform->getName()) {
            case 'postgresql':
                $this->_conn->executeQuery('CREATE EXTENSION IF NOT EXISTS postgis;');
                break;
        }
        $this->fixtureLoader = new Loader();

        $config = Setup::createAnnotationMetadataConfiguration([ __DIR__ . '/Fixtures' ], false);
        $this->em = EntityManager::create($this->_conn, $config, $this->platform->getEventManager());
        $this->schemaTool = new SchemaTool($this->em);

        $this->schemaTool->updateSchema([
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\MultiLineStringEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\MultiPointEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\PointEntity'),
            $this->em->getClassMetadata('Brick\Geo\Tests\Doctrine\Fixtures\PolygonEntity')
        ]);

        $purger = new ORMPurger();
        $this->ormExecutor = new ORMExecutor($this->em, $purger);
    }

    protected function getEntityManager() {
        return $this->em;
    }

    protected function addFixture(FixtureInterface $fixture)
    {
        $this->fixtureLoader->addFixture($fixture);
    }

    protected function loadFixtures()
    {
        $this->ormExecutor->execute($this->fixtureLoader->getFixtures());
    }
}