<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\LinearRing;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPolygonEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMultiPolygonData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        $point1 = Point::factory(0,0);
        $point2 = Point::factory(1,0);
        $point3 = Point::factory(1,1);
        $point4 = Point::factory(0,1);
        $point5 = Point::factory(0,0);
        $ring1 = LinearRing::factory([ $point1, $point2, $point3, $point4, $point5]);
        $poly1 = Polygon::factory([ $ring1 ]);

        $point6 = Point::factory(2,2);
        $point7 = Point::factory(3,2);
        $point8 = Point::factory(3,3);
        $point9 = Point::factory(2,3);
        $point10 = Point::factory(2,2);
        $ring2 = LinearRing::factory([ $point6, $point7, $point8, $point9, $point10]);
        $poly2 = Polygon::factory([ $ring2 ]);

        $multiPoly1 = new MultiPolygonEntity();
        $multiPoly1->setMultiPolygon(MultiPolygon::factory([ $poly1, $poly2 ]));

        $manager->persist($multiPoly1);
        $manager->flush();
    }
}
