<?php

namespace Brick\Geo\Tests;

use Brick\Geo\TIN;
use Brick\Geo\Triangle;

/**
 * Unit tests for class TIN.
 */
class TINTest extends AbstractTestCase
{
    public function testCreate()
    {
        $triangle1 = Triangle::fromText('TRIANGLE ((1 1, 1 2, 2 2, 1 1))');
        $triangle2 = Triangle::fromText('TRIANGLE ((1 1, 2 2, 2 1, 1 1))');

        $tin = TIN::create([$triangle1, $triangle2], false, false);
        $this->assertWktEquals($tin, 'TIN (((1 1, 1 2, 2 2, 1 1)), ((1 1, 2 2, 2 1, 1 1)))');
    }
}
