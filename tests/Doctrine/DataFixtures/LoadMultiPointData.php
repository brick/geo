<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\MultiPoint;
use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\MultiPointEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadMultiPointData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $point1 = Point::xy(0,0);
        $point2 = Point::xy(1,0);
        $point3 = Point::xy(1,1);

        $multiPoint1 = new MultiPointEntity();
        $multiPoint1->setMultiPoint(MultiPoint::of($point1, $point2, $point3));

        $manager->persist($multiPoint1);
        $manager->flush();
    }
}
