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
    public function load(ObjectManager $manager)
    {
        $point1 = Point::xy(0,0);
        $point2 = Point::xy(1,0);
        $point3 = Point::xy(1,1);
        $lineString1 = LineString::of($point1, $point2, $point3);

        $point4 = Point::xy(2,2);
        $point5 = Point::xy(3,2);
        $point6 = Point::xy(3,3);
        $lineString2 = LineString::of($point4, $point5, $point6);

        $multilineString1 = new MultiLineStringEntity();
        $multilineString1->setMultiLineString(MultiLineString::of($lineString1, $lineString2));

        $manager->persist($multilineString1);
        $manager->flush();
    }
}
