<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\PointEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadPointData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $point1 = new PointEntity();
        $point1->setPoint(Point::xy(0, 0));

        $manager->persist($point1);
        $manager->flush();
    }
}
