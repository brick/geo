<?php

namespace Brick\Geo\Tests\Doctrine\Functions;

use Brick\Geo\Point;
use Brick\Geo\Tests\Doctrine\Fixtures\PointEntity;
use Brick\Geo\Tests\Doctrine\FunctionalTestCase;

/**
 * Tests for GreatCircleDistance Doctrine function.
 */
class GreatCircleDistanceFunctionTest extends FunctionalTestCase
{
    /**
     * @dataProvider providerGreatCircleDistanceFunction
     *
     * @param string $pointA           The WKT of the first point, with Lon/Lat coordinates.
     * @param string $pointB           The WKT of the second point, with Lon/Lat coordinates.
     * @param float  $expectedDistance The expected distance in meters.
     */
    public function testGreatCircleDistanceFunction($pointA, $pointB, $expectedDistance)
    {
        $pointA = Point::fromText($pointA, 4326);
        $pointB = Point::fromText($pointB, 4326);

        $em = $this->getEntityManager();
        $em->beginTransaction();

        $em->createQueryBuilder()
            ->delete()
            ->from(PointEntity::class, 'p')
            ->getQuery()
            ->execute();

        $pointEntity = new PointEntity();
        $pointEntity->setPoint($pointA);

        $em->persist($pointEntity);
        $em->flush($pointEntity);

        $actualDistance = $em->createQueryBuilder()
            ->select('GreatCircleDistance(p.point, :point)')
            ->from(PointEntity::class, 'p')
            ->setParameter('point', $pointB, 'point')
            ->getQuery()
            ->getSingleScalarResult();

        $em->rollback();

        $this->assertEquals($expectedDistance, $actualDistance, '', 100.0);
    }

    /**
     * @return array
     */
    public function providerGreatCircleDistanceFunction()
    {
        $paris      = 'POINT (2.35 48.85)';
        $london     = 'POINT (0.13 51.50)';
        $losAngeles = 'POINT (118.25 34.05)';
        $newYork    = 'POINT (73.93 40.67)';
        $helsinki   = 'POINT (24.93 60.17)';
        $melbourne  = 'POINT (144.97 37.82)';

        return [
            [$paris,      $paris,      0.0],
            [$paris,      $london,     334361.1],
            [$paris,      $losAngeles, 8832066.2],
            [$paris,      $newYork,    5512676.9],
            [$paris,      $helsinki,   1908747.1],
            [$paris,      $melbourne,  9697371.8],
            [$london,     $paris,      334361.1],
            [$london,     $london,     0.0],
            [$london,     $losAngeles, 8756577.4],
            [$london,     $newYork,    5568330.0],
            [$london,     $helsinki,   1807698.4],
            [$london,     $melbourne,  9511051.2],
            [$losAngeles, $paris,      8832066.2],
            [$losAngeles, $london,     8756577.4],
            [$losAngeles, $losAngeles, 0.0],
            [$losAngeles, $newYork,    3943085.8],
            [$losAngeles, $helsinki,   6948879.1],
            [$losAngeles, $melbourne,  2433324.7],
            [$newYork,    $paris,      5512676.9],
            [$newYork,    $london,     5568330.0],
            [$newYork,    $losAngeles, 3943085.8],
            [$newYork,    $newYork,    0.0],
            [$newYork,    $helsinki,   3960993.6],
            [$newYork,    $melbourne,  5953229.4],
            [$helsinki,   $paris,      1908747.1],
            [$helsinki,   $london,     1807698.4],
            [$helsinki,   $losAngeles, 6948879.1],
            [$helsinki,   $newYork,    3960993.6],
            [$helsinki,   $helsinki,   0.0],
            [$helsinki,   $melbourne,  7829616.9],
            [$melbourne,  $paris,      9697371.8],
            [$melbourne,  $london,     9511051.2],
            [$melbourne,  $losAngeles, 2433324.7],
            [$melbourne,  $newYork,    5953229.4],
            [$melbourne,  $helsinki,   7829616.9],
            [$melbourne,  $melbourne,  0.0],
        ];
    }
}
