<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Engine;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for DatabaseDriver implementations.
 */
class DatabaseDriverTest extends AbstractTestCase
{
    public function testConvertBinaryResult() : void
    {
        $binary = $this->getDatabaseDriver()->executeQuery("SELECT ST_AsBinary(ST_GeomFromText('POINT(1 2)'))")->get(0)->asBinary();
        self::assertSame('0101000000000000000000f03f0000000000000040', bin2hex($binary));
    }

    #[DataProvider('providerConvertStringResult')]
    public function testConvertStringResult(string $string) : void
    {
        $actual = $this->getDatabaseDriver()->executeQuery('SELECT ', new ScalarValue($string))->get(0)->asString();
        self::assertSame($string, $actual);
    }

    public static function providerConvertStringResult() : array
    {
        return [
            [''],
            [' '],
            ['0'],
            ['1'],
            ['true'],
            ['false'],
            ['1.25'],
            ['foobar'],
            ["'"],
            ['"'],
            ['\\'],
        ];
    }

    #[DataProvider('providerConvertIntResult')]
    public function testConvertIntResult(string $intAsString, int $expected) : void
    {
        $actual = $this->getDatabaseDriver()->executeQuery("SELECT $intAsString")->get(0)->asInt();
        self::assertSame($expected, $actual);
    }

    public static function providerConvertIntResult() : array
    {
        return [
            ['-123', -123],
            ['-12', -12],
            ['-1', -1],
            ['0', 0],
            ['1', 1],
            ['12', 12],
            ['123', 123],
        ];
    }

    #[DataProvider('providerConvertFloatResult')]
    public function testConvertFloatResult(string $floatAsString, float $expected) : void
    {
        $actual = $this->getDatabaseDriver()->executeQuery("SELECT $floatAsString")->get(0)->asFloat();
        self::assertSame($expected, $actual);
    }

    public static function providerConvertFloatResult() : array
    {
        return [
            ['-1.25', -1.25],
            ['-1.0', -1.0],
            ['0.0', 0.0],
            ['1.0', 1.0],
            ['1.25', 1.25],
        ];
    }

    #[DataProvider('providerConvertBoolResult')]
    public function testConvertBoolResult(string $boolAsString, bool $expected) : void
    {
        $actual = $this->getDatabaseDriver()->executeQuery("SELECT $boolAsString")->get(0)->asBool();
        self::assertSame($expected, $actual);
    }

    public static function providerConvertBoolResult() : array
    {
        return [
            ['true', true],
            ['false', false],
        ];
    }

    private function getDatabaseDriver(): DatabaseDriver
    {
        if (! isset($GLOBALS['DATABASE_DRIVER'])) {
            self::markTestSkipped('This test requires a database driver to be set.');
        }

        return $GLOBALS['DATABASE_DRIVER'];
    }
}
