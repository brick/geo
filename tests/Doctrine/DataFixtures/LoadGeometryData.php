<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeometryData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $point1 = new GeometryEntity();
        $point1->setGeometry(Point::xy(0, 0));

        $manager->persist($point1);
        $manager->flush();
    }
}
