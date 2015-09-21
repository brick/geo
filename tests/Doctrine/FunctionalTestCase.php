<?php

namespace Brick\Geo\Tests\Doctrine;

use Brick\Geo\Doctrine\Functions\EarthDistanceFunction;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\PDOEngine;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;
use Doctrine\Tests\DbalFunctionalTestCase;

/**
 * Base class for Doctrine types functional test cases.
 */
abstract class FunctionalTestCase extends DbalFunctionalTestCase
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

        if (! GeometryEngineRegistry::has()) {
            $this->markTestSkipped('This test requires a connection to a database.');
        }

        $engine = GeometryEngineRegistry::get();

        if (! $engine instanceof PDOEngine) {
            $this->markTestSkipped('This test currently only works with a PDO connection.');
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

        $config->addCustomNumericFunction('EarthDistance', EarthDistanceFunction::class);

        $this->em = EntityManager::create($this->_conn, $config, $this->platform->getEventManager());
        $this->schemaTool = new SchemaTool($this->em);

        $this->schemaTool->updateSchema([
            $this->em->getClassMetadata(Fixtures\GeometryEntity::class),
            $this->em->getClassMetadata(Fixtures\LineStringEntity::class),
            $this->em->getClassMetadata(Fixtures\MultiLineStringEntity::class),
            $this->em->getClassMetadata(Fixtures\MultiPointEntity::class),
            $this->em->getClassMetadata(Fixtures\MultiPolygonEntity::class),
            $this->em->getClassMetadata(Fixtures\PointEntity::class),
            $this->em->getClassMetadata(Fixtures\PolygonEntity::class)
        ]);

        $purger = new ORMPurger();
        $this->ormExecutor = new ORMExecutor($this->em, $purger);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param FixtureInterface $fixture
     *
     * @return void
     */
    protected function addFixture(FixtureInterface $fixture)
    {
        $this->fixtureLoader->addFixture($fixture);
    }

    /**
     * @return void
     */
    protected function loadFixtures()
    {
        $this->ormExecutor->execute($this->fixtureLoader->getFixtures());
    }

    /**
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     *
     * @return void
     */
    protected function assertPointEquals(Point $point, $x, $y, $z = null)
    {
        $this->assertInstanceOf(Point::class, $point);

        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
    }
}
