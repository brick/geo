<?php


declare(strict_types=1);

namespace Brick\Geo\Tests\Engine;

use Brick\Geo\Engine\Database\Driver\Internal\TypeConverter;
use Brick\Geo\Exception\GeometryEngineException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TypeConverter.
 */
class TypeConverterTest extends TestCase
{
    #[DataProvider('providerConvertStringToInt')]
    public function testConvertStringToInt(string $string, int $expected) : void
    {
        $actual = TypeConverter::convertStringToInt($string);
        self::assertSame($expected, $actual);
    }

    public static function providerConvertStringToInt() : array
    {
        return [
            [(string) PHP_INT_MIN, PHP_INT_MIN],
            ['-123456789', -123456789],
            ['-123', -123],
            ['-12', -12],
            ['-1', -1],
            ['0', 0],
            ['1', 1],
            ['12', 12],
            ['123', 123],
            ['123456789', 123456789],
            [(string) PHP_INT_MAX, PHP_INT_MAX],
        ];
    }
    #[DataProvider('providerConvertStringToIntThrowsException')]
    public function testConvertStringToIntThrowsException(string $string, string $expectedMessage) : void
    {
        $this->expectException(GeometryEngineException::class);
        $this->expectExceptionMessage($expectedMessage);

        TypeConverter::convertStringToInt($string);
    }

    public static function providerConvertStringToIntThrowsException() : array
    {
        return [
            ['', "The database returned an unexpected type: expected integer string, got ''."],
            ['foo', "The database returned an unexpected type: expected integer string, got 'foo'."],
            ['-0', "The database returned an unexpected type: expected integer string, got '-0'."],
            ['1.0', "The database returned an unexpected type: expected integer string, got '1.0'."],
            ['123 ', "The database returned an unexpected type: expected integer string, got '123 '."],
            [' 123', "The database returned an unexpected type: expected integer string, got ' 123'."],
            [' 123 ', "The database returned an unexpected type: expected integer string, got ' 123 '."],
            ['123456789012345678901234567890', 'The database return an out of range integer: 123456789012345678901234567890'],
        ];
    }
}
