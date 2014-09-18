Brick\Geo
=========

A collection of classes to work with GIS geometries.

[![Build Status](https://secure.travis-ci.org/brick/geo.png?branch=master)](http://travis-ci.org/brick/geo)
[![Coverage Status](https://coveralls.io/repos/brick/geo/badge.png?branch=master)](https://coveralls.io/r/brick/geo?branch=master)

Introduction
------------

This library is an implementation of the [OpenGIS specification](http://www.opengeospatial.org/standards/sfa).

Installation
------------

This library is installable via [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "brick/geo": "dev-master"
        }
    }

Requirements
------------

This library requires PHP 5.5 or higher. [HHVM](http://hhvm.com/) is officially supported.

This library is essentially a wrapper around a third-party GIS engine, and delegates the complexity of the
geometry calculations to a `GeometryService` implementation. The following implementations are available:

- `PDOService`: communicates with a compatible GIS database over a `PDO` connection. The following databases are currently supported:
  - [MySQL](http://php.net/manual/en/ref.pdo-mysql.php) version 5.6 or greater (earlier versions only have a partial GIS support based on bounding boxes)
  - [PostgreSQL](http://php.net/manual/en/ref.pdo-pgsql.php) *with the [PostGIS](http://postgis.net/install) extension installed*
- `SQLite3Service`: communicates with a [SQLite](http://php.net/manual/en/book.sqlite3.php) database, *with the [SpatiaLite](https://www.gaia-gis.it/fossil/libspatialite/index) extension loaded*
- `GEOSService`: uses the [GEOS](https://github.com/libgeos/libgeos) PHP bindings. You will need to compile the GEOS engine with the `--enable-php` flag and add the `geos.so` extension to your php.ini

You will need to configure one of these services to use the advanced functions of the library.

If you try to use a function that requires such a service, and none is set, this will result in a `GeometryException`.

**You may already have access to a GIS engine** if your project uses one of the supported databases.
For example, if you're using MySQL 5.6 or 5.7 over a `PDO` connection, just put this code in your bootstrap file:

    use Brick\Geo\Service\GeometryServiceRegistry;
    use Brick\Geo\Service\PDOService;

    GeometryServiceRegistry::set(new PDOService($pdo));

And you will have the full power of GIS readily available!

Overview
--------

To be written.
