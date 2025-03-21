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

It provides [Geometry classes](#geometry-hierarchy) (`Point`, `LineString`, `Polygon`, etc.), and can natively read/write many formats: WKB, WKT, EWKB, EWKT, and GeoJSON.

It also provides a `GeometryEngine` interface for advanced calculations (`length`, `area`, `union`, `intersection`, etc.),
together with implementations that delegate these operations to a third-party GIS engine:

- the [**GEOS**](https://git.osgeo.org/gitea/geos/php-geos) extension,
- the [**geosop**](https://libgeos.org/usage/tools/#geosop) command-line tool,
- or a GIS-enabled database: **MySQL**, **MariaDB**, **PostgreSQL** with PostGIS, or **SQLite** with SpatiaLite.

Requirements and installation
-----------------------------

This library requires PHP 8.1.
For PHP 8.0, you can use version `0.9`.
For PHP 7.4, you can use version `0.7`.

Install the library with [Composer](https://getcomposer.org/):

```bash
composer require brick/geo
```

If you only need basic operations such as building Geometry objects, importing from / exporting to one of the supported formats (WKB, WKT, EWKB, EWKT, or GeoJSON), then you're all set.

If you need advanced features, such as `length()`, `union()`, `intersection`, etc., head on to the [Configuration](#configuration) section to choose a `GeometryEngine` implementation.

Project status & release process
--------------------------------

This library is still under development.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.13.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/brick/geo/releases) for a list of changes introduced by each further `0.x.0` version.

Quick start
-----------

```php
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

// Building geometries from coordinates

$lineString = LineString::of(
    Point::xy(1, 2),
    Point::xy(3, 4),
);

echo $lineString->asText(); // LINESTRING (1 2, 3 4)

// Importing geometries

$point = Point::fromText('POINT (1 2)');

echo $point->x(); // 1
echo $point->y(); // 2

// Using advanced calculations from a GeometryEngine
// (see the Configuration section)

$polygon = Polygon::fromText('POLYGON ((0 0, 0 3, 3 3, 0 0))');
echo $geometryEngine->area($polygon); // 4.5

$centroid = $geometryEngine->centroid($polygon);
echo $centroid->asText(); // POINT (1 2)
```

Configuration
-------------

Advanced calculations are available through the `GeometryEngine` interface. The library ships with the following implementations:

- `PdoEngine`: communicates with a GIS-compatible database over a `PDO` connection.  
  This engine currently supports the following databases:
  - [MySQL](http://php.net/manual/en/ref.pdo-mysql.php) version 5.6 or greater (*2D geometries only*)
  - MariaDB version 5.5 or greater
  - [PostgreSQL](http://php.net/manual/en/ref.pdo-pgsql.php) with the [PostGIS](http://postgis.net/) extension
- `Sqlite3Engine`: communicates with an [SQLite3](http://php.net/manual/en/book.sqlite3.php) database with the [SpatiaLite](https://www.gaia-gis.it/fossil/libspatialite/index) extension
- `GeosEngine`: uses the [GEOS](https://git.osgeo.org/gitea/geos/php-geos) PHP extension
- `GeosOpEngine`: uses the [geosop](https://libgeos.org/usage/tools/#geosop) command-line tool

Your choice for the right implementation should be guided by two criteria:

- **availability**: if you already use a GIS-enabled database such as MySQL, this may be an easy choice;
- **capabilities**: not all databases offer the same GIS capabilities:
  - some functions may be available on PostgreSQL but not on other databases (see the [GeometryEngine methods reference](#geometryengine-methods-reference) section)
  - some functions may be restricted to certain geometry types and/or SRIDs; for example, `buffer()` works on MySQL, but would fail with a `Polygon` on SRID 4326 (GPS coordinates, distance in meters)
  - some databases may return distances in meters on SRID 4326, while others may return distances in degrees

You should probably start with the easiest method that works for you, and test if this setup matches your expectations.

Click on one of the following configurations to see the setup instructions:

<details>
<summary>Using <strong>PDO</strong> and <strong>MySQL</strong></summary>

- Ensure that your MySQL version is at least `5.6`.  
  Earlier versions only have partial GIS support based on bounding boxes and are not supported.
- Use this bootstrap code in your project:

    ```php
    use Brick\Geo\Engine\PdoEngine;
    
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $geometryEngine = new PdoEngine($pdo);
    ```

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).
</details>

<details>
<summary>Using <strong>PDO</strong> and <strong>MariaDB</strong></summary>

- Ensure that your MariaDB version is at least `5.5`.
- MariaDB is a fork of MySQL, so you can follow the same procedure as for MySQL.
</details>

<details>
<summary>Using <strong>PDO</strong> and <strong>PostgreSQL</strong> with <strong>PostGIS</strong></summary>

- Ensure that [PostGIS is installed](http://postgis.net/install/) on your server
- Enable PostGIS on the database server if needed:

        CREATE EXTENSION postgis;

- Use this bootstrap code in your project:

    ```php
    use Brick\Geo\Engine\PdoEngine;
    
    $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
    $geometryEngine = new PdoEngine($pdo);
    ```

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).
</details>

<details>
<summary>Using <strong>PDO</strong> and <strong>SQLite</strong> with <strong>SpatiaLite</strong></summary>

Due to [limitations in the pdo_sqlite driver](https://bugs.php.net/bug.php?id=64810), it is currently not possible<sup>*</sup> to load the SpatiaLite extension with a
`SELECT LOAD_EXTENSION()` query, hence you cannot use SpatiaLite with the PDO driver.

You need to use the SQLite3 driver instead. Note that you can keep using your existing PDO SQLite code,
all you need to do is create an additional in-memory SQLite3 database just to power the geometry engine.

<sup>* It actually *is* possible, using [moxio/sqlite-extended-api](https://github.com/Moxio/sqlite-extended-api), which uses FFI and [Z-Engine](https://github.com/lisachenko/z-engine), but beware that this library is still experimental!</sup>
</details>

<details>
<summary>Using <strong>SQLite3</strong> with <strong>SpatiaLite</strong></summary>

- Ensure that [SpatiaLite is installed](https://www.gaia-gis.it/fossil/libspatialite/index) on your system.
- Ensure that the SQLite3 extension is enabled in your `php.ini`:

        extension=sqlite3.so

- Ensure that the SQLite3 extension dir where SpatiaLite is installed is configured in your `php.ini`:

        [sqlite3]
        sqlite3.extension_dir = /usr/lib

- Use this bootstrap code in your project:

    ```php
    use Brick\Geo\Engine\Sqlite3Engine;
    
    $sqlite3 = new SQLite3(':memory:');
    $sqlite3->loadExtension('mod_spatialite.so');
    $geometryEngine = new Sqlite3Engine($sqlite3);
    ```

- Depending on the functions you use, you will probably need to initialize the spatial metadata by running this query:

    ```sql
    SELECT InitSpatialMetaData();
    ```
  
  You only need to run this query once if your database is persisted, but **if your database is in-memory, you'll need to run it on every connection**. Be aware that this may hurt performance.

In this example we have created an in-memory database for our GIS calculations, but you can also use an existing `SQLite3` connection.
</details>

<details>
<summary>Using the <strong>GEOS</strong> extension</summary>

- Ensure that the [GEOS extension](https://git.osgeo.org/gitea/geos/php-geos) is installed on your server (GEOS 3.6.0 onwards; previous versions require compiling GEOS with the `--enable-php` flag).
- Ensure that the extension is enabled in your `php.ini`:

        extension=geos.so

- Use this bootstrap code in your project:

    ```php
    use Brick\Geo\Engine\GeosEngine;
    
    $geometryEngine = new GeosEngine();
    ```
</details>

<details>
<summary>Using the <strong><code>geosop</code></strong> command-line tool</summary>

- Ensure that [geosop](https://libgeos.org/usage/tools/#geosop) is installed on your server.
  You can install it on Fedora with the `geos` package, or on Ubuntu / Debian with the `geos-bin` package.
- Use this bootstrap code in your project:

    ```php
    use Brick\Geo\Engine\GeosOpEngine;
    
    $geometryEngine = new GeosOpEngine('/usr/bin/geosop');
    ```

Adjust the path to the `geosop` binary if needed.

Note that every call to the `GeosOpEngine` will spawn a new process, which comes with a performance overhead compared to the other engines.
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
      - [Tin](https://github.com/brick/geo/blob/master/src/Tin.php)
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
- `GeometryIoException` is thrown when an error occurs while reading or writing (E)WKB/T or GeoJSON data
- `InvalidGeometryException` is thrown when creating an invalid geometry, such as a `LineString` with only one `Point`
- `NoSuchGeometryException` is thrown when attempting to get a geometry at a non-existing index in a collection
- `UnexpectedGeometryException` is thrown when a geometry is not an instance of the expected subtype, for example when
calling `Point::fromText()` with a `LineString` WKT.

GeometryEngine methods reference
--------------------------------

This is a list of all methods available in the `GeometryEngine` interface. Some methods are only available
if you use a specific geometry engine, sometimes with a minimum version.

| Function Name           | GEOS | geosop | PostGIS | MySQL  | MariaDB | SpatiaLite |
|-------------------------|------|--------|---------|--------|---------|------------|
| `area`                  | ✓    |        | ✓       | ✓      | ✓       | ✓          |
| `azimuth`               |      |        | ✓       |        |         | ✓          |
| `boundary`              | ✓    | ✓      | ✓       |        |         | ✓          |
| `buffer`                | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `centroid`              | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `contains`              | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `concaveHull`           |      | ✓      | ✓       |        |         | ✓          |
| `convexHull`            | ✓    | ✓      | ✓       | 5.7.6  |         | ✓          |
| `crosses`               | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |
| `difference`            | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `disjoint`              | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |
| `distance`              | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `envelope`              | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |
| `equals`                | ✓    | 3.11.0 | ✓       | ✓      | ✓       | ✓          |
| `intersection`          | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `intersects`            | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `isClosed`              | ✓    |        | ✓       | ✓      | ✓       | ✓          |
| `isRing`                | ✓    |        | ✓       | ✓      | ✓       | ✓          |
| `isSimple`              | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `isValid`               | ✓    | ✓      | ✓       | 5.7.6  |         | ✓          |
| `length`                | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `lineInterpolatePoint`  | ✓    |        | ✓       | 8.0.24 |         | ✓          |
| `lineInterpolatePoints` |      |        | ✓       | 8.0.24 |         |            |
| `locateAlong`           |      |        | ✓       |        |         | ✓          |
| `locateBetween`         |      |        | ✓       |        |         | ✓          |
| `makeValid`             |      | ✓      | ✓       |        |         | ✓          |
| `maxDistance`           |      |        | ✓       |        |         | ✓          |
| `overlaps`              | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |
| `pointOnSurface`        | ✓    |        | ✓       |        |         | ✓          |
| `relate`                | ✓    |        | ✓       |        |         | ✓          |
| `simplify`              | ✓    |        | ✓       | 5.7.6  |         | 4.1.0      |
| `snapToGrid`            |      |        | ✓       |        |         | ✓          |
| `split`                 |      |        | ✓       |        |         | ✓          |
| `symDifference`         | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `touches`               | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |
| `transform`             | ✓    |        | ✓       | 8.0.13 | ✓       | ✓          |
| `union`                 | ✓    | ✓      | ✓       | ✓      | ✓       | ✓          |
| `within`                | ✓    | 3.12.0 | ✓       | ✓      | ✓       | ✓          |

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

You can alternatively use the [WktReader](https://github.com/brick/geo/blob/master/src/Io/WktReader.php) and
[WktWriter](https://github.com/brick/geo/blob/master/src/Io/WktWriter.php) classes directly; the latter allows you to
pretty-print the output.

### WKB

Well-Known Binary is the standard binary format for geometries.

Every Geometry class provides a convenience method `fromBinary()`, that accepts a WKB binary string and an optional
SRID, and returns a Geometry object:

```php
use Brick\Geo\Point;

$point = Point::fromBinary(hex2bin('0101000000000000000000f83f0000000000000440'), 4326);

echo $point->asText(); // POINT (1.5 2.5)
echo $point->srid(); // 4326
```

Geometries can be converted to WKB using the convenience method `asBinary()`:

```php
echo bin2hex($point->asBinary()); // 0101000000000000000000f83f0000000000000440
```

You can alternatively use the [WkbReader](https://github.com/brick/geo/blob/master/src/Io/WkbReader.php) and
[WkbWriter](https://github.com/brick/geo/blob/master/src/Io/WkbWriter.php) classes directly; the latter allows you to
choose the endianness of the output (big endian or little endian).

### EWKT

Extended WKT is a PostGIS-specific text format that includes the SRID of the geometry object, which is missing from the
standard WKT format. You can import from and export to this format using the
[EwktReader](https://github.com/brick/geo/blob/master/src/Io/EwktReader.php) and
[EwktWriter](https://github.com/brick/geo/blob/master/src/Io/EwktWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\Io\EwktReader;
use Brick\Geo\Io\EwktWriter;

$reader = new EwktReader();
$point = $reader->read('SRID=4326; POINT (1.5 2.5)');

echo $point->asText(); // POINT (1.5 2.5)
echo $point->srid(); // 4326

$writer = new EwktWriter();
echo $writer->write($point); // SRID=4326; POINT (1.5 2.5)
```

### EWKB

Extended WKB is a PostGIS-specific binary format that includes the SRID of the geometry object, which is missing from
the standard WKB format. You can import from and export to this format using the
[EwkbReader](https://github.com/brick/geo/blob/master/src/Io/EwkbReader.php) and
[EwkbWriter](https://github.com/brick/geo/blob/master/src/Io/EwkbWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwkbWriter;

$reader = new EwkbReader();
$point = $reader->read(hex2bin('0101000020e6100000000000000000f83f0000000000000440'));

echo $point->asText(); // POINT (1.5 2.5)
echo $point->srid(); // 4326

$writer = new EwkbWriter();
echo bin2hex($writer->write($point)); // 0101000020e6100000000000000000f83f0000000000000440
```

### GeoJSON

GeoJSON is an open standard format designed for representing simple geographical features, based on JSON, and
standardized in [RFC 7946](https://tools.ietf.org/html/rfc7946).

This library supports importing geometries from, and exporting them to GeoJSON documents using the
[GeoJsonReader](https://github.com/brick/geo/blob/master/src/Io/GeoJsonReader.php) and
[GeoJsonWriter](https://github.com/brick/geo/blob/master/src/Io/GeoJsonWriter.php) classes:

```php
use Brick\Geo\Point;
use Brick\Geo\Io\GeoJsonReader;
use Brick\Geo\Io\GeoJsonWriter;

$reader = new GeoJsonReader();
$point = $reader->read('{ "type": "Point", "coordinates": [1, 2] }');

echo $point->asText(); // POINT (1 2)
echo $point->srid(); // 4326

$writer = new GeoJsonWriter();
echo $writer->write($point); // {"type":"Point","coordinates":[1,2]}
```

The library supports reading and writing `Feature` and `FeatureCollection` objects, together with custom properties.

GeoJSON aims to support WGS84 only, and as such all Geometries are imported using [SRID 4326](https://epsg.io/4326).

## Reducing coordinate precision

Before exporting geometries in a text format, you may need to reduce the precision of the coordinates to keep the output small, while retaining a sufficient precision.
You can use the `withRoundedCoordinates()` method for this:

```php
use Brick\Geo\Point;
use Brick\Geo\Projector\RoundCoordinatesProjector;

$point = Point::xy(1.2345678, 2.3456789);
echo $point->asText(); // POINT (1.2345678 2.3456789)

$roundedPoint = $point->withRoundedCoordinates(2);
echo $roundedPoint->asText(); // POINT (1.23 2.35)
```

## Doctrine mappings

You can use `brick/geo` types in your Doctrine entities using the [brick/geo-doctrine](https://github.com/brick/geo-doctrine) package.
