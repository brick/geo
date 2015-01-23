<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGeometryData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        $point1 = new GeometryEntity();
        $point1->setGeometry(Point::factory(0, 0));

        $manager->persist($point1);
        $manager->flush();
    }
}