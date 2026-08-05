<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

/**
 * @see https://dev.mysql.com/doc/refman/8.0/en/gis-wkb-functions.html
 *
 * @warning Not supported by MariaDB
 */
enum MysqlGeoAxisOrder: string
{
    case SridDefined = 'srid-defined'; // Default
    case LatLong = 'lat-long';
    case LongLat = 'long-lat';
}
