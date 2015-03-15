<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\LineStringEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadLineStringData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        $point1 = Point::xy(0,0);
        $point2 = Point::xy(1,0);
        $point3 = Point::xy(1,1);

        $lineString1 = new LineStringEntity();
        $lineString1->setLineString(LineString::factory([ $point1, $point2, $point3 ]));

        $manager->persist($lineString1);
        $manager->flush();
    }
}
