Brick\Geo
=========

A collection of classes to work with GIS geometries.

[![Build Status](https://secure.travis-ci.org/brick/geo.svg?branch=master)](http://travis-ci.org/brick/geo)
[![Coverage Status](https://coveralls.io/repos/brick/geo/badge.svg?branch=master)](https://coveralls.io/r/brick/geo?branch=master)

Introduction
------------

This library is a PHP implementation of the [OpenGIS specification](http://www.opengeospatial.org/standards/sfa).

It is essentially a wrapper around a third-party GIS engine, to which it delegates most of the complexity of the
geometry calculations. Several engines are supported, from native PHP extensions such as GEOS to GIS-compatible databases such as MySQL or PostgreSQL.

Requirements and installation
-----------------------------

This library requires PHP 5.5 or higher. [HHVM](http://hhvm.com/) is officially supported.

We recommend installing it with [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "brick/geo": "dev-master"
        }
    }

Then head on to the [Configuration](#configuration) section to configure a GIS geometry engine.

Failure to configure a geometry engine would result in a `GeometryException` being thrown when trying to use a method that requires one.

Configuration
-------------

Configuring the library consists in choosing the most convenient `GeometryEngine` implementation for your installation. The following implementations are available:

- `PDOEngine`: communicates with a GIS-compatible database over a `PDO` connection.  
  This engine currently supports the following databases:
  - [MySQL](http://php.net/manual/en/ref.pdo-mysql.php) version 5.6 or greater.  
    *Note: MySQL currently only supports 2D geometries.*
    
  - [PostgreSQL](http://php.net/manual/en/ref.pdo-pgsql.php) with the [PostGIS](http://postgis.net/install) extension.
- `SQLite3Engine`: communicates with a [SQLite3](http://php.net/manual/en/book.sqlite3.php) database with the [SpatiaLite](https://www.gaia-gis.it/fossil/libspatialite/index) extension.
- `GEOSEngine`: uses the [GEOS](https://github.com/libgeos/libgeos) PHP bindings.

Following is a step-by-step guide for all the possible configurations:

### Using PDO and MySQL 5.6 or greater

- Ensure that your MySQL version is at least `5.6`.  
  Earlier versions only have partial GIS support based on bounding boxes and are not supported.
- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\PDOEngine;

        $pdo = new PDO('mysql:host=localhost', 'root', '');
        GeometryEngineRegistry::set(new PDOEngine($pdo));

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).

### Using PDO and MariaDB 5.5 or greater

MariaDB is a fork of MySQL, so you can follow the same procedure as for MySQL.
Just ensure that your MariaDB version is `5.5` or greater.

### Using PDO and PostgreSQL with PostGIS

- Ensure that [PostGIS is installed](http://postgis.net/install/) on your server
- Enable PostGIS on the database server if needed:

        CREATE EXTENSION postgis;

- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\PDOEngine;

        $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
        GeometryEngineRegistry::set(new PDOEngine($pdo));

Update the code with your own connection parameters, or use an existing `PDO` connection if you have one (recommended).

### Using PDO and SQLite with SpatiaLite

Due to [limitations in the PDO_SQLITE driver](https://bugs.php.net/bug.php?id=64810), it is currently not possible to load the SpatiaLite extension with a
`SELECT LOAD_EXTENSION()` query, hence you cannot use SpatiaLite with the PDO driver.

You need to use the SQLite3 driver instead. Note that you can keep using your existing PDO_SQLITE code,
all you need to do is create an additional in-memory SQLite3 database just to power the geometry engine.

### Using SQLite3 with SpatiaLite

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
        $sqlite3->loadExtension('libspatialite.so.3');
        GeometryEngineRegistry::set(new SQLite3Engine($sqlite3));

Update the `libspatialite` extension name as required. In this example we have created an in-memory database for our GIS calculations, but you can also use an existing `SQLite3` connection.

### Using GEOS PHP bindings

- Ensure that [GEOS is installed](https://github.com/libgeos/libgeos) on your server. GEOS must have been compiled with the `--enable-php` flag to provide the PHP extension.
- Ensure that the GEOS extension is enabled in your `php.ini`:

        extension=geos.so

- Use this bootstrap code in your project:

        use Brick\Geo\Engine\GeometryEngineRegistry;
        use Brick\Geo\Engine\GEOSEngine;

        GeometryEngineRegistry::set(new GEOSEngine());

Geometry hierarchy
------------------

All geometry objects extend a base `Geometry` class:

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
