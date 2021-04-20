<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\GeometryEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeometryData implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $point1 = new GeometryEntity();
        $point1->setGeometry(Point::xy(1, 2));

        $manager->persist($point1);
        $manager->flush();
    }
}
