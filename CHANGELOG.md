# Changelog

## [0.11.3](https://github.com/brick/geo/releases/tag/0.11.3) - 2025-02-19

✨ **New features**

- The following methods have been added:
  - `CircularString::withAddedPoints()`
  - `CompoundCurve::withAddedCurves()`
  - `CurvePolygon::withExteriorRing()`, `withInteriorRings()`, `withAddedInteriorRings()`
  - `GeometryCollection::withAddedGeometries()`
  - `LineString::withAddedPoints()`
  - `Polygon::withExteriorRing()`, `withInteriorRings()`, `withAddedInteriorRings()`
  - `PolyhedralSurface::withAddedPatches()`

## [0.11.2](https://github.com/brick/geo/releases/tag/0.11.2) - 2025-02-18

🐛 **Bug fixes**

- extending an empty `BoundingBox` would get its immutability violated by setting a coordinate system

## [0.11.1](https://github.com/brick/geo/releases/tag/0.11.1) - 2024-08-19

✨ **New features**

- New projector: `RoundCoordinatesProjector`

## [0.11.0](https://github.com/brick/geo/releases/tag/0.11.0) - 2024-06-07

💥 **Breaking changes**

- interface `GeometryEngine` has a new method: `split()`
- method `GeometryEngine::boundingPolygons()` has been removed

✨ **New features**

- New engine method: `GeometryEngine::split()`

## [0.10.0](https://github.com/brick/geo/releases/tag/0.10.0) - 2024-01-23

💥 **Breaking changes**

- **Minimum PHP version is now `8.1`**
- interface `GeometryEngine` has a new method: `makeValid()`
- `AbstractWKBWriter::setByteOrder()` now accepts a `WKBByteOrder` enum instead of a `WKBTools::*_ENDIAN` constant
- constants `WKBTools::BIG_ENDIAN` and `WKBTools::LITTLE_ENDIAN` have been removed
- method `WKBTools::checkByteOrder()` has been removed

✨ **New features**

- New engine method: `GeometryEngine::makeValid()`

## [0.9.0](https://github.com/brick/geo/releases/tag/0.9.0) - 2022-11-22

💥 **Breaking changes**

- Proxies are now `@internal` and considered an implementation detail. This means that they are no longer part of the public API and can change at any time. This means that you should *always* type-hint against the base class and not the proxy.
- `Point` constructor now throws an exception when passing `NaN` or infinite coordinates.
- `GeometryEngine::pointOnSurface()` now returns `Point` instead of `Geometry`.

🐛 **Bug fixes**

- `Geometry::withSRID()` now properly propagates the SRID to all sub-geometries. Previously, only the top-level geometry was updated.
- `Point` constructor would error when destructuring an associative array.

✨ **New features**

- New `Projector` API to reproject geometry coordinates.
- New method: `Polygon::rings()`
- New method: `Geometry::isIdenticalTo()`

✨ **Improvements**

- `GeometryCollection::geometryN()` is now properly documented with generics.

## [0.8.1](https://github.com/brick/geo/releases/tag/0.8.1) - 2022-10-14

✨ **Improvements**

- Better Psalm annotations for geometry getters
- Removed outdated suggestion in `composer.json`
- Minor documentation fixes & proxy code style improvements

## [0.8.0](https://github.com/brick/geo/releases/tag/0.8.0) - 2022-10-11

💥 **Breaking changes**

**Minimum PHP version is now `8.0`.**

The global `GeometryEngineRegistry` is gone. All convenience methods that rely on the `GeometryEngineRegistry` have been removed.
You should now explicitly call the `GeometryEngine`, that you can get injected with your dependency injection container.

For example, the following call:

```php
$lineString->length();
```

Should be replaced with:

```php
$geometryEngine->length($lineString);
```

**Detail of breaking changes**

The following class has been removed:

- `GeometryEngineRegistry`

The following method has been added:

- `GeometryEngine::isRing()`

The following method signatures have been changed:

- `GeometryEngine::boundingPolygons()`

The following deprecated methods have been removed:

- `Curve::isClosed()`
- `Curve::isRing()`
- `Curve::length()`
- `Geometry::boundary()`
- `Geometry::buffer()`
- `Geometry::centroid()`
- `Geometry::contains()`
- `Geometry::convexHull()`
- `Geometry::crosses()`
- `Geometry::difference()`
- `Geometry::disjoint()`
- `Geometry::distance()`
- `Geometry::equals()`
- `Geometry::envelope()`
- `Geometry::intersection()`
- `Geometry::intersects()`
- `Geometry::isSimple()`
- `Geometry::isValid()`
- `Geometry::locateAlong()`
- `Geometry::locateBetween()`
- `Geometry::maxDistance()`
- `Geometry::overlaps()`
- `Geometry::relate()`
- `Geometry::simplify()`
- `Geometry::snapToGrid()`
- `Geometry::symDifference()`
- `Geometry::touches()`
- `Geometry::transform()`
- `Geometry::union()`
- `Geometry::within()`
- `MultiCurve::isClosed()`
- `MultiCurve::length()`
- `MultiSurface::area()`
- `MultiSurface::pointOnSurface()`
- `Point::azimuth()`
- `PolyhedralSurface::boundingPolygons()`
- `PolyhedralSurface::isClosed()`
- `Surface::area()`
- `Surface::pointOnSurface()`

The following change only affects you if you implement a custom `DatabaseEngine`:

- The signature of `DatabaseEngine::getParameterPlaceholder()` has changed

💥 **Non-breaking changes**

- The following signatures have changed, but are not a breaking change due to LSP:
  - `DatabaseEngine::getParameterPlaceholder(mixed $parameter): string`
  - `GeometryEngine::length(Curve|MultiCurve $g): float`
  - `GeometryEngine::area(Surface|MultiSurface $g): float`
  - `GeometryEngine::pointOnSurface(Surface|MultiSurface $g): Geometry`

🐛 **Fixes**

- Fixed a potential bug when destructuring associative arrays in Geometry class constructors

## [0.7.2](https://github.com/brick/geo/releases/tag/0.7.2) - 2022-10-10

🗑️ **Deprecations**

**The `GeometryEngineRegistry` class has been deprecated.** All convenience methods on Geometry classes that rely on the
`GeometryEngineRegistry` are deprecated, and **will be removed in version `0.8`**.

You should now explicitly call the `GeometryEngine`, that you can get injected with your dependency injection container.

Deprecated methods:

- `Curve::isClosed()`
- `Curve::isRing()`
- `Curve::length()`
- `Geometry::boundary()`
- `Geometry::buffer()`
- `Geometry::centroid()`
- `Geometry::contains()`
- `Geometry::convexHull()`
- `Geometry::crosses()`
- `Geometry::difference()`
- `Geometry::disjoint()`
- `Geometry::distance()`
- `Geometry::equals()`
- `Geometry::envelope()`
- `Geometry::intersection()`
- `Geometry::intersects()`
- `Geometry::isSimple()`
- `Geometry::isValid()`
- `Geometry::locateAlong()`
- `Geometry::locateBetween()`
- `Geometry::maxDistance()`
- `Geometry::overlaps()`
- `Geometry::relate()`
- `Geometry::simplify()`
- `Geometry::snapToGrid()`
- `Geometry::symDifference()`
- `Geometry::touches()`
- `Geometry::transform()`
- `Geometry::union()`
- `Geometry::within()`
- `MultiCurve::isClosed()`
- `MultiCurve::length()`
- `MultiSurface::area()`
- `MultiSurface::pointOnSurface()`
- `Point::azimuth()`
- `PolyhedralSurface::boundingPolygons()`
- `PolyhedralSurface::isClosed()`
- `Surface::area()`
- `Surface::pointOnSurface()`

## [0.7.1](https://github.com/brick/geo/releases/tag/0.7.1) - 2021-11-06

🐛 **Fixes**

- Fix a deprecation warning on PHP 8.1 (#35)

## [0.7.0](https://github.com/brick/geo/releases/tag/0.7.0) - 2021-10-10

💥 **Breaking change**

The Doctrine mapping types & functions have been moved to a separate package.

If you don't use these, you don't need to do anything to upgrade.
If you use them, just require the [brick/geo-doctrine](https://github.com/brick/geo-doctrine) package in addition to `brick/geo`.

## [0.6.2](https://github.com/brick/geo/releases/tag/0.6.2) - 2021-08-17

🐛 **Fixes**

- Fixed bug when retrieving `GeometryCollection` on MySQL 8 using `DatabaseEngine` (#33)

## [0.6.1](https://github.com/brick/geo/releases/tag/0.6.1) - 2021-04-20

🐛 **Fixes**

- Fixed bug with PDO MySQL when using non-emulated prepared statements

## [0.6.0](https://github.com/brick/geo/releases/tag/0.6.0) - 2021-03-16

✨ **New features**

- **Proper support for `Feature` and `FeatureCollection` in `GeoJSONReader` and `GeoJSONWriter`**
- **Support for auto-calculating the `bbox` attribute in `GeoJSONWriter`**
- New method: `Geometry::transform()` transforms `Geometry` coordinates to a new SRID
- New method: `Geometry::toXY()` returns a new `Geometry` with no `Z` and `M` coordinates
- New method: `Geometry::withoutZ()` returns a new `Geometry` with the `Z` coordinate removed
- New method: `Geometry::withoutM()` returns a new `Geometry` with the `M` coordinate removed
- New method: `Geometry::getBoundingBox()` returns the south-west and north-east bounds of a Geometry
- New method: `CoordinateSystem::isEqualTo()` compares against another `CoordinateSystem`

🐛 **Fixes**

- Doctrine types could hydrate a parent Geometry proxy class, but now hydrate the correct Geometry proxy subclass by introspecting the WKB without fully loading it

✨ **Improvements**

- Proxy data is now always sent as is to the `DatabaseEngine`

💥 **BC breaks**

- **Minimum PHP version is now 7.4**
- New signature for `CoordinateSystemException::sridMix()`
- New signature for `CoordinateSystemException::dimensionalityMix()`

The following breaks only affect you if you use the GeoJSON reader/writer:

- `GeoJSONReader` now instantiates Features and FeatureCollections as `Feature` and `FeatureCollection` objects, instead of `Geometry` and `GeometryCollection` objects
- `GeoJSONWriter` will now write GeometryCollections as `GeometryCollection` type, instead of `FeatureCollection`

The following breaks will only affect you if you're writing your own geometry engine, or your own WKB reader:

- `AbstractWKBReader::readGeometryHeader()` signature was changed
- `WKBReader::read()` signature was changed
- `GeometryEngine` has a new `transform()` method
- `ProxyInterface` has a new `isProxyBinary()` method

## [0.5.1](https://github.com/brick/geo/releases/tag/0.5.1) - 2021-04-20

🐛 **Fixes**

- Fixed bug with PDO MySQL when using non-emulated prepared statements

## [0.5.0](https://github.com/brick/geo/releases/tag/0.5.0) - 2021-03-05

🐛 **Fixes**

- Fixed illegal parameter data type issue on MariaDB

💥 **BC breaks**

Note: these breaks will likely not affect you, unless you're writing your own geometry engine.

- `DatabaseEngine::$useProxy` is now `private`, and must be provided through a parent constructor call.
- `DatabaseEngine::executeQuery()`, when provided with geometry data, now takes `GeometryParameter` objects instead of `Geometry` objects directly.

## [0.4.0](https://github.com/brick/geo/releases/tag/0.4.0) - 2020-12-29

✨ **New features**

- New method `Point::azimuth()` (#17) thanks to @Kolyunya
- `centroid()` is now available on the root `Geometry` class (#20) thanks to @Kolyunya
- [Psalm](https://psalm.dev/) annotations on the whole codebase

🐛 **Fixes**

- Fixed wrongly documented return types

💥 **BC breaks**

Note: these breaks will likely not affect you, unless you're writing your own geometry engine or WK(B|T) parser.

- `GeometryEngine` interface adds an `azimuth()` method
- `GeometryEngine::centroid()` now returns `Point`
- constants in `WKTParser` / `EWKTParser` are now `protected`
- `WBKBuffer::readDoubles()`'s `$count` parameter is now typed

## [0.3.0](https://github.com/brick/geo/releases/tag/0.3.0) - 2020-12-14

✨ **New features**

- **compatibility with PHP 8**
- compatibility with brick/reflection `0.4`

💥 **Breaking changes**

- **minimum PHP version is now 7.2**
- deprecated Doctrine function `EarthDistanceFunction` has been removed

Earth distance calculations should be delegated to the geometry engine, that should be able to handle geographic computations; MySQL 8, for example, supports calculating distances in meters between two SRID 4326 points.

## [0.2.6](https://github.com/brick/geo/releases/tag/0.2.6) - 2019-12-24

**Deprecations**

Doctrine function `EarthDistanceFunction` is now deprecated, and **will be removed in `0.3.0`**.

**Improvements**

This version extends compatibility to `brick/reflection` version `0.3`.

## [0.2.5](https://github.com/brick/geo/releases/tag/0.2.5) - 2019-03-30

**New methods**

- `Geometry::withSRID()`
- `CoordinateSystem::withSRID()`

These methods return a copy of the original object, with the SRID altered.

## [0.2.4](https://github.com/brick/geo/releases/tag/0.2.4) - 2019-03-30

**New method**: `Geometry::swapXY()`

This methods returns a copy of the Geometry, with X and Y coordinates swapped. It is useful when needing to convert geometries from `Lat, Lng` to `Lng, Lat` and conversely.

## [0.2.3](https://github.com/brick/geo/releases/tag/0.2.3) - 2019-01-26

Improvements to GeoJSON reader and writer classes:

- `GeoJSONReader` can now be lenient with documents containing wrong case types, such as `POINT` instead of `Point`:

```php
$reader = new GeoJSONReader(true); // case-insensitive
```

- `GeoJSONWriter` can now pretty-print the JSON output:

```php
$writer = new GeoJSONWriter(true); // pretty-print
```

## [0.2.2](https://github.com/brick/geo/releases/tag/0.2.2) - 2019-01-24

This version adds support for importing from and exporting to GeoJSON:

- `Brick\Geo\IO\GeoJSONReader`
- `Brick\Geo\IO\GeoJSONWriter`

Thanks @michaelcurry 👍

## [0.2.1](https://github.com/brick/geo/releases/tag/0.2.1) - 2017-11-08

Fixed a potential Error when an Exception is expected in `WKBReader` and `EWKBReader`.

## [0.2.0](https://github.com/brick/geo/releases/tag/0.2.0) - 2017-10-03

Minimum PHP version is now **7.1**.

## [0.1.0](https://github.com/brick/geo/releases/tag/0.1.0) - 2017-10-03

First beta release.

