Brick\Geo
=========

<img src="https://raw.githubusercontent.com/brick/brick/master/logo.png" alt="" align="left" height="64">

A GIS geometry library for PHP.


[![Build Status](https://github.com/brick/geo/workflows/CI/badge.svg)](https://github.com/brick/geo/actions)
[![Coverage Status](https://coveralls.io/repos/github/brick/geo/badge.svg?branch=master)](https://coveralls.io/github/brick/geo?branch=master)
[![Latest Stable Version](https://poser.pugx.org/brick/geo/v/stable)](https://packagist.org/packages/brick/geo)
[![Total Downloads](https://poser.pugx.org/brick/geo/downloads)](https://packagist.org/packages/brick/geo)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

Introduction
------------

This library is a PHP implementation of the [OpenGIS specification](http://www.opengeospatial.org/standards/sfa).

It is essentially a wrapper around a third-party GIS engine, to which it delegates most of the complexity of the
geometry calculations. Several engines are supported, from native PHP extensions such as GEOS to GIS-compatible databases such as MySQL or PostgreSQL.

Already using a MySQL 5.6+ or MariaDB 5.5+ database? Just provide the library with a `PDO` instance and unlock the full power of GIS calculations.

Not using MySQL/MariaDB? You still have a few options to choose from!

Requirements and installation
-----------------------------

This library requires 7.2 or later.

Install the library with [Composer](https://getcomposer.org/):

```bash
composer require brick/geo
```

Then head on to the [Configuration](#configuration) section to configure a GIS geometry engine.

Failure to configure a geometry engine would result in a `GeometryEngineException` being thrown when trying to use a method that requires one.

Project status & release process
--------------------------------

This library is still under development.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.2.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/brick/geo/releases) for a list of changes introduced by each further `0.x.0` version.

Configuration
-------------

Configuring the library consists in choosing the most convenient `GeometryEngine` implementation for your installation. The following implementations are available:

- `PDOEngine`: communicates with a GIS-compatible database over a `PDO` connection.  
  This engine currently supports the following databases:
  - [MySQL](http://php.net/manual/en/ref.pdo-mysql.php) version 5.6 or greater.  
    *Note: MySQL currently only supports 2D geometries.*
  - MariaDB version 5.5 or greater.
  - [PostgreSQL](http://php.net/manual/en/ref.pdo-pgsql.php) with the [PostGIS](http://postgis.net/install) extension.
- `SQLite3Engine`: communicates with a [SQLite3](http://php.net/manual/en/book.sqlite3.php) database with the [SpatiaLite](https://www.gaia-gis.it/fossil/libspatialite/index) extension.
- `GEOSEngine`: uses the [GEOS](https://github.com/libgeos/libgeos) PHP bindings.

Following is a step-by-step guide for all the possible configurations:

### Using PDO and MySQL 5.6 or greater

<details>
<summary>Click to expand</summary>

- Ensure that your MySQL version is at least `5.6`.  
  Earlier versions only have partial GIS support based on bounding boxes and are not supported.
- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\PDOEngine;

        $pdo = new PDO('mysql:host=localhost', 'root', '');
        GeometryEngineRegistry::set(new PDOEngine($pdo));

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).
</details>

### Using PDO and MariaDB 5.5 or greater

<details>
<summary>Click to expand</summary>

MariaDB is a fork of MySQL, so you can follow the same procedure as for MySQL.
Just ensure that your MariaDB version is `5.5` or greater.
</details>

### Using PDO and PostgreSQL with PostGIS

<details>
<summary>Click to expand</summary>

- Ensure that [PostGIS is installed](http://postgis.net/install/) on your server
- Enable PostGIS on the database server if needed:

        CREATE EXTENSION postgis;

- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\PDOEngine;

        $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
        GeometryEngineRegistry::set(new PDOEngine($pdo));

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).
</details>

### Using PDO and SQLite with SpatiaLite

<details>
<summary>Click to expand</summary>

Due to [limitations in the PDO_SQLITE driver](https://bugs.php.net/bug.php?id=64810), it is currently not possible to load the SpatiaLite extension with a
`SELECT LOAD_EXTENSION()` query, hence you cannot use SpatiaLite with the PDO driver.

You need to use the SQLite3 driver instead. Note that you can keep using your existing PDO_SQLITE code,
all you need to do is create an additional in-memory SQLite3 database just to power the geometry engine.
</details>

### Using SQLite3 with SpatiaLite

<details>
<summary>Click to expand</summary>

- Ensure that [SpatiaLite is installed](https://www.gaia-gis.it/fossil/libspatialite/index) on your system.
- Ensure that the SQLite3 extension is enabled in your `php.ini`:

        extension=sqlite3.so

- Ensure that the SQLite3 extension dir where SpatiaLite is installed is configured in your `php.ini`:

        [sqlite3]
        sqlite3.extension_dir = /usr/lib

- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\SQLite3Engine;

        $sqlite3 = new SQLite3(':memory:');
        $sqlite3->loadExtension('mod_spatialite.so');
        GeometryEngineRegistry::set(new SQLite3Engine($sqlite3));

In this example we have created an in-memory database for our GIS calculations, but you can also use an existing `SQLite3` connection.
</details>

### Using GEOS PHP bindings

<details>
<summary>Click to expand</summary>

- Ensure that [the PHP bindings for GEOS](https://git.osgeo.org/gogs/geos/php-geos) are installed on your server (GEOS 3.6.0 onwards; previous versions require compiling GEOS with the `--enable-php` flag).
- Ensure that the GEOS extension is enabled in your `php.ini`:

        extension=geos.so

- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\GEOSEngine;

        GeometryEngineRegistry::set(new GEOSEngine());
</details>

Geometry hierarchy
------------------

All geometry objects reside in the `Brick\Geo` namespace, and extend a base `Geometry` class:

- [Geometry](https://github.com/brick/geo/blob/master/src/Geometry.php) `abstract`
  - [Point](https://github.com/brick/geo/blob/master/src/Point.php)
  - [Curve](https://github.com/brick/geo/blob/master/src/Curve.php) `abstract`
    - [LineString](https://github.com/brick/geo/blob/master/src/LineString.php)
    - [CompoundCurve](https://github.com/brick/geo/blob/master/src/CompoundCurve.php)
    - [CircularString](https://github.com/brick/geo/blob/master/src/CircularString.php)
  - [Surface](https://github.com/brick/geo/blob/master/src/Surface.php) `abstract`
    - [Polygon](https://github.com/brick/geo/blob/master/src/Polygon.php)
      - [Triangle](https://github.com/brick/geo/blob/master/src/Triangle.php)
    - [CurvePolygon](https://github.com/brick/geo/blob/master/src/CurvePolygon.php)
    - [PolyhedralSurface](https://github.com/brick/geo/blob/master/src/PolyhedralSurface.php)
      - [TIN](https://github.com/brick/geo/blob/master/src/TIN.php)
  - [GeometryCollection](https://github.com/brick/geo/blob/master/src/GeometryCollection.php)
    - [MultiPoint](https://github.com/brick/geo/blob/master/src/MultiPoint.php)
    - [MultiCurve](https://github.com/brick/geo/blob/master/src/MultiCurve.php) `abstract`
      - [MultiLineString](https://github.com/brick/geo/blob/master/src/MultiLineString.php)
    - [MultiSurface](https://github.com/brick/geo/blob/master/src/MultiSurface.php) `abstract`
      - [MultiPolygon](https://github.com/brick/geo/blob/master/src/MultiPolygon.php)

Geometry exceptions
-------------------

All geometry exceptions reside in the `Brick\Geo\Exception` namespace, and extend a base `GeometryException` object.

Geometry exceptions are fine-grained: only subclasses of the base `GeometryException` class are thrown throughout
the project. This leaves to the user the choice to catch only specific exceptions, or all geometry-related exceptions.

Here is a list of all exceptions:

- `CoordinateSystemException` is thrown when mixing objects with different SRID or dimensionality (e.g. XY with XYZ)
- `EmptyGeometryException` is thrown when trying to access a non-existent property on an empty geometry
- `GeometryEngineException` is thrown when a functionality is not supported by the current geometry engine
- `GeometryIOException` is thrown when an error occurs while reading or writing (E)WKB/T data
- `InvalidGeometryException` is thrown when creating an invalid geometry, such as a `LineString` with only one `Point`
- `NoSuchGeometryException` is thrown when attempting to get a geometry at an non-existing index in a collection
- `UnexpectedGeometryException` is thrown when a geometry is not an instance of the expected sub-type, for example when
calling `Point::fromText()` with a `LineString` WKT.

Example
-------

    use Brick\Geo\Polygon;

    $polygon = Polygon::fromText('POLYGON ((0 0, 0 3, 3 3, 0 0))');
    echo $polygon->area(); // 4.5

    $centroid = $polygon->centroid();
    echo $centroid->asText(); // POINT (1 2)

Spatial Function Reference
--------------------------

This is a list of all functions which are currently implemented in the geo project. Some functions are only available
if you use a specific geometry engine, sometimes with a minimum version.
This table also shows which functions are part of the OpenGIS standard.

| Function Name    | GEOS | PostGIS | MySQL  | MariaDB | SpatiaLite | OpenGIS standard |
|------------------|------|---------|--------|---------|------------|------------------|
| `area`           |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `boundary`       |  ✓   |    ✓    |        |        |     ✓      |        ✓         |
| `buffer`         |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `centroid`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `contains`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `convexHull`     |  ✓   |    ✓    | 5.7.6  |        |     ✓      |        ✓         |
| `crosses`        |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `difference`     |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `disjoint`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `distance`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `envelope`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `equals`         |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `intersects`     |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `intersection`   |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `isSimple`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `isValid`        |  ✓   |    ✓    | 5.7.6  |        |     ✓      |                  |
| `length`         |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `locateAlong`    |      |    ✓    |        |        |     ✓      |                  |
| `locateBetween`  |      |    ✓    |        |        |     ✓      |                  |
| `maxDistance`    |      |    ✓    |        |        |     ✓      |                  |
| `overlaps`       |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `pointOnSurface` |  ✓   |    ✓    |        |        |     ✓      |        ✓         |
| `relate`         |  ✓   |    ✓    |        |        |     ✓      |        ✓         |
| `simplify`       |  ✓   |    ✓    | 5.7.6  |        |    4.1.0    |                  |
| `snapToGrid`     |      |    ✓    |        |         |     ✓      |                  |
| `symDifference`  |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `touches`        |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `union`          |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |
| `within`         |  ✓   |    ✓    |   ✓    |   ✓    |     ✓      |        ✓         |

Importing and exporting geometries
----------------------------------

This library supports importing from and exporting to the following formats:

- WKT
- WKB
- EWKT
- EWKB
- GeoJSON

### WKT

Well-Known Text is the standard text format for geometries.

Every Geometry class provides a convenience method `fromText()`, that accepts a WKT string and an optional SRID, and
returns a Geometry object:

```php
use Brick\Geo\Point;

$point = Point::fromText('POINT (1.5 2.5)', 4326);
```

Geometries can be converted to WKT using the convenience method `asText()`:

```php
echo $point->asText(); // POINT (1.5 2.5)
```

You can alternatively use the [WKTReader](https://github.com/brick/geo/blob/master/src/IO/WKTReader.php) and
[WKTWriter](https://github.com/brick/geo/blob/master/src/IO/WKTWriter.php) classes directly; the latter allows you to
pretty-print the output.

### WKB

Well-Known Binary is the standard binary format for geometries.

Every Geometry class provides a convenience method `fromBinary()`, that accepts a WKB binary string and an optional
SRID, and returns a Geometry object:

```php
use Brick\Geo\Point;

$point = Point::fromBinary(hex2bin('0101000000000000000000f83f0000000000000440'), 4326);

echo $point->asText(); // POINT (1.5 2.5)
echo $point->SRID(); // 4326
```

Geometries can be converted to WKB using the convenience method `asBinary()`:

```php
echo bin2hex($point->asBinary()); // 0101000000000000000000f83f0000000000000440
```

You can alternatively use the [WKBReader](https://github.com/brick/geo/blob/master/src/IO/WKBReader.php) and
[WKBWriter](https://github.com/brick/geo/blob/master/src/IO/WKBWriter.php) classes directly; the latter allows you to
choose the endianness of the output (big endian or little endian).

### EWKT

Extended WKT is a PostGIS-specific text format that includes the SRID of the geometry object, which is missing from the
standard WKT format. You can import from and export to this format using the
[EWKTReader](https://github.com/brick/geo/blob/master/src/IO/EWKTReader.php) and
[EWKTWriter](https://github.com/brick/geo/blob/master/src/IO/EWKTWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\IO\EWKTReader;
use Brick\Geo\IO\EWKTWriter;

$reader = new EWKTReader();
$point = $reader->read('SRID=4326; POINT (1.5 2.5)');

echo $point->asText(); // POINT (1.5 2.5)
echo $point->SRID(); // 4326

$writer = new EWKTWriter();
echo $writer->write($point); // SRID=4326; POINT (1.5 2.5)
```

### EWKB

Extended WKB is a PostGIS-specific binary format that includes the SRID of the geometry object, which is missing from
the standard WKB format. You can import from and export to this format using the
[EWKBReader](https://github.com/brick/geo/blob/master/src/IO/EWKBReader.php) and
[EWKBWriter](https://github.com/brick/geo/blob/master/src/IO/EWKBWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\IO\EWKBReader;
use Brick\Geo\IO\EWKBWriter;

$reader = new EWKBReader();
$point = $reader->read(hex2bin('0101000020e6100000000000000000f83f0000000000000440'));

echo $point->asText(); // POINT (1.5 2.5)
echo $point->SRID(); // 4326

$writer = new EWKBWriter();
echo bin2hex($writer->write($point)); // 0101000020e6100000000000000000f83f0000000000000440
```

### GeoJSON

GeoJSON is an open standard format designed for representing simple geographical features, based on JSON, and
standardized in [RFC 7946](https://tools.ietf.org/html/rfc7946).

This library supports importing geometries from, and exporting them to GeoJSON documents using the
[GeoJSONReader](https://github.com/brick/geo/blob/master/src/IO/GeoJSONReader.php) and
[GeoJSONWriter](https://github.com/brick/geo/blob/master/src/IO/GeoJSONWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSONWriter;

$reader = new GeoJSONReader();
$point = $reader->read('{ "type": "Point", "coordinates": [1, 2] }');

echo $point->asText(); // POINT (1 2)
echo $point->SRID(); // 4326

$writer = new GeoJSONWriter();
echo $writer->write($point); // {"type":"Point","coordinates":[1,2]}
```

Note that `Feature`s are imported as `Geometry` objects, and `FeatureCollection`s are imported as `GeometryCollection`
objects. Non-spatial attributes are ignored.

GeoJSON aims to support WGS84 only, and as such all Geometries are imported using [SRID 4326](https://epsg.io/4326).
