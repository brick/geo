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

Many functions in this library are not yet implemented natively, and delegate calculations to a database with GIS support.

The following databases are currently supported:

- [MySQL](http://dev.mysql.com/downloads/mysql/) 5.6 or greater via [PDO](http://php.net/manual/en/ref.pdo-mysql.php)
- [PostgreSQL](http://www.postgresql.org/download/) with the [PostGIS](http://postgis.net/install) extension via [PDO](http://php.net/manual/en/ref.pdo-pgsql.php)
- [SQLite](http://www.sqlite.org/) with the [SpatiaLite](https://www.gaia-gis.it/fossil/libspatialite/index) extension via the [SQLite3](http://php.net/manual/en/book.sqlite3.php) class

Overview
--------

To be written.
