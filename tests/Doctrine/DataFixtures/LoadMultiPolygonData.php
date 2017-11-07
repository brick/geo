<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\LineString;
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
    public function load(ObjectManager $manager)
    {
        $point1 = Point::xy(0,0);
        $point2 = Point::xy(1,0);
        $point3 = Point::xy(1,1);
        $point4 = Point::xy(0,1);
        $point5 = Point::xy(0,0);

        $ring1 = LineString::of($point1, $point2, $point3, $point4, $point5);
        $poly1 = Polygon::of($ring1);

        $point6 = Point::xy(2,2);
        $point7 = Point::xy(3,2);
        $point8 = Point::xy(3,3);
        $point9 = Point::xy(2,3);
        $point10 = Point::xy(2,2);

        $ring2 = LineString::of($point6, $point7, $point8, $point9, $point10);
        $poly2 = Polygon::of($ring2);

        $multiPoly1 = new MultiPolygonEntity();
        $multiPoly1->setMultiPolygon(MultiPolygon::of($poly1, $poly2));

        $manager->persist($multiPoly1);
        $manager->flush();
    }
}
