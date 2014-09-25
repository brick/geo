<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Point;
use Brick\Geo\IO\WKBReader;
use Brick\Geo\Tests\AbstractTestCase;

/**
 * Unit tests for class WKBReader.
 */
class WKBReaderTest extends AbstractTestCase
{
    public function testReadPoint()
    {
        /** @var Point $point */
        $point = WKBReader::read(hex2bin('010100000000000000000018400000000000001C40'));
        $this->assertPointEquals([6, 7], false, false, $point);
    }
}
