<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\TIN;
use Brick\Geo\Triangle;

/**
 * Unit tests for class TIN.
 */
class TINTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testCreate() : void
    {
        $triangle1 = Triangle::fromText('TRIANGLE ((1 1, 1 2, 2 2, 1 1))');
        $triangle2 = Triangle::fromText('TRIANGLE ((1 1, 2 2, 2 1, 1 1))');

        $tin = TIN::of($triangle1, $triangle2);
        $this->assertWktEquals($tin, 'TIN (((1 1, 1 2, 2 2, 1 1)), ((1 1, 2 2, 2 1, 1 1)))');
    }
}
