<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiLineStringEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMultiLineStringData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        $point1 = Point::factory(0,0);
        $point2 = Point::factory(1,0);
        $point3 = Point::factory(1,1);
        $lineString1 = LineString::factory([ $point1, $point2, $point3 ]);

        $point4 = Point::factory(2,2);
        $point5 = Point::factory(3,2);
        $point6 = Point::factory(3,3);
        $lineString2 = LineString::factory([ $point4, $point5, $point6 ]);

        $multilineString1 = new MultiLineStringEntity();
        $multilineString1->setMultiLineString(MultiLineString::factory([ $lineString1, $lineString2 ]));

        $manager->persist($multilineString1);
        $manager->flush();
    }
}
