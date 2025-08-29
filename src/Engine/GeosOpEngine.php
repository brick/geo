<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Surface;
use Override;

use function error_reporting;
use function explode;
use function fclose;
use function is_numeric;
use function is_resource;
use function is_string;
use function preg_match;
use function proc_close;
use function proc_open;
use function rtrim;
use function sprintf;
use function stream_get_contents;

/**
 * GeometryEngine implementation based on the geosop binary.
 *
 * https://libgeos.org/usage/tools/#geosop
 */
final class GeosOpEngine implements GeometryEngine
{
    public function __construct(
        /** Path to the geosop binary. */
        private readonly string $geosopPath,
    ) {
    }

    #[Override]
    public function union(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('union', [$a, $b], Geometry::class);
    }

    #[Override]
    public function difference(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('difference', [$a, $b], Geometry::class);
    }

    #[Override]
    public function envelope(Geometry $g): Geometry
    {
        return $this->queryGeometry('envelope', [$g], Geometry::class);
    }

    #[Override]
    public function length(Curve|MultiCurve $g): float
    {
        return $this->queryFloat('length', [$g]);
    }

    #[Override]
    public function area(Surface|MultiSurface $g): float
    {
        // geosop does have an area operation, but it is broken (return a geometry).
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function azimuth(Point $observer, Point $subject): float
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function centroid(Geometry $g): Point
    {
        return $this->queryGeometry('centroid', [$g], Point::class);
    }

    #[Override]
    public function pointOnSurface(Surface|MultiSurface $g): Point
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function boundary(Geometry $g): Geometry
    {
        return $this->queryGeometry('boundary', [$g], Geometry::class);
    }

    #[Override]
    public function isValid(Geometry $g): bool
    {
        return $this->queryBoolean('isValid', [$g]);
    }

    #[Override]
    public function isClosed(Geometry $g): bool
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function isSimple(Geometry $g): bool
    {
        return $this->queryBoolean('isSimple', [$g]);
    }

    #[Override]
    public function isRing(Curve $curve): bool
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function makeValid(Geometry $g): Geometry
    {
        return $this->queryGeometry('makeValid', [$g], Geometry::class);
    }

    #[Override]
    public function equals(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('equals', [$a, $b]);
    }

    #[Override]
    public function disjoint(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('disjoint', [$a, $b]);
    }

    #[Override]
    public function intersects(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('intersects', [$a, $b]);
    }

    #[Override]
    public function touches(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('touches', [$a, $b]);
    }

    #[Override]
    public function crosses(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('crosses', [$a, $b]);
    }

    #[Override]
    public function within(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('within', [$a, $b]);
    }

    #[Override]
    public function contains(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('contains', [$a, $b]);
    }

    #[Override]
    public function overlaps(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('overlaps', [$a, $b]);
    }

    #[Override]
    public function relate(Geometry $a, Geometry $b, string $matrix): bool
    {
        // geosop has a relate operation, but no support for matrix.
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function locateAlong(Geometry $g, float $mValue): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function locateBetween(Geometry $g, float $mStart, float $mEnd): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function distance(Geometry $a, Geometry $b): float
    {
        return $this->queryFloat('distance', [$a, $b]);
    }

    #[Override]
    public function buffer(Geometry $g, float $distance): Geometry
    {
        return $this->queryGeometry('buffer', [$g, $distance], Geometry::class);
    }

    #[Override]
    public function convexHull(Geometry $g): Geometry
    {
        return $this->queryGeometry('convexHull', [$g], Geometry::class);
    }

    #[Override]
    public function concaveHull(Geometry $g, float $convexity, bool $allowHoles): Geometry
    {
        if ($allowHoles) {
            throw new GeometryEngineException('geosop does not support concaveHull with holes.');
        }

        return $this->queryGeometry('concaveHull', [$g, $convexity], Geometry::class);
    }

    #[Override]
    public function intersection(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('intersection', [$a, $b], Geometry::class);
    }

    #[Override]
    public function symDifference(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('symDifference', [$a, $b], Geometry::class);
    }

    #[Override]
    public function snapToGrid(Geometry $g, float $size): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function simplify(Geometry $g, float $tolerance): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function maxDistance(Geometry $a, Geometry $b): float
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function transform(Geometry $g, int $srid): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function split(Geometry $g, Geometry $blade): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction): Point
    {
        // Unlike the GEOS PHP extension, interpolate has no support normalized=true, which we need here.
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function lineInterpolatePoints(LineString $lineString, float $fraction): MultiPoint
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    /**
     * @throws GeometryEngineException
     */
    public function getGeosOpVersion(): string
    {
        // No --version yet, we have to parse the first line of the output!
        $output = $this->execute([]);
        $lines = explode("\n", $output);

        $firstLine = $lines[0];

        if (preg_match('/^geosop - GEOS (\S+)$/', $firstLine, $matches) !== 1) {
            throw new GeometryEngineException(sprintf('Failed to parse geosop version from output: "%s"', $firstLine));
        }

        return $matches[1];
    }

    /**
     * @param list<string> $arguments The CLI arguments for geosop.
     *
     * @return string The stdout output.
     *
     * @throws GeometryEngineException
     */
    private function execute(array $arguments): string
    {
        $descriptors = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        // Mute warnings and back up the current error reporting level.
        $errorReportingLevel = error_reporting(0);

        try {
            $command = [$this->geosopPath, ...$arguments];
            $process = proc_open($command, $descriptors, $pipes);

            if (! is_resource($process)) {
                throw new GeometryEngineException("Failed to run geosop at path: $this->geosopPath");
            }

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            if ($stdout === false) {
                throw new GeometryEngineException('Failed to read geosop stdout');
            }

            if ($stderr === false) {
                throw new GeometryEngineException('Failed to read geosop stderr');
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
        } finally {
            // Restore the error reporting level.
            error_reporting($errorReportingLevel);
        }

        if ($exitCode !== 0 || $stderr !== '') {
            if ($exitCode !== 0) {
                if ($stderr !== '') {
                    $message = sprintf('geosop failed with exit code %d and error: %s', $exitCode, $stderr);
                } else {
                    $message = sprintf('geosop failed with exit code %d and no error output', $exitCode);
                }
            } else {
                $message = sprintf('geosop failed with error: %s', $stderr);
            }

            throw new GeometryEngineException($message);
        }

        return $stdout;
    }

    /**
     * @param 'wkt'|'txt'                 $format
     * @param list<Geometry|string|float> $arguments
     *
     * @throws GeometryEngineException
     */
    private function query(string $operation, array $arguments, string $format): string
    {
        $arguments = $this->buildArguments($operation, $format, $arguments);

        $output = $this->execute($arguments);
        $output = rtrim($output);

        if ($output === '') {
            throw new GeometryEngineException('geosop did not return any output');
        }

        return $output;
    }

    /**
     * Examples:
     *
     * ('length', 'wkt', [Geometry]) => ['-f', 'wkt', '-a', 'WKT of Geometry', 'length']
     * ('union', 'wkt', [Geometry, Geometry]) => ['-f', 'wkt', '-a', 'WKT of Geometry 1', '-b', 'WKT of Geometry 2', 'union']
     * ('buffer', 'txt', [Geometry, float]) => ['-f', 'txt', '-a', 'WKT of Geometry', 'buffer', 'float as string']
     *
     * @param 'wkt'|'txt'                 $format
     * @param list<Geometry|string|float> $arguments
     *
     * @return list<string>
     */
    private function buildArguments(string $operation, string $format, array $arguments): array
    {
        $geometryArgs = [];
        $otherArgs = [];

        $numberOfGeometries = 0;

        foreach ($arguments as $argument) {
            if ($argument instanceof Geometry) {
                $geometryArgs[] = match (++$numberOfGeometries) {
                    1 => '-a',
                    2 => '-b',
                };

                $geometryArgs[] = $argument->asText();
            } elseif (is_string($argument)) {
                $otherArgs[] = $argument;
            } else {
                $otherArgs[] = (string) $argument;
            }
        }

        return ['-f', $format, ...$geometryArgs, $operation, ...$otherArgs];
    }

    /**
     * @template T of Geometry
     *
     * @param list<Geometry|string|float> $arguments
     * @param class-string<T>             $geometryClass
     *
     * @return T
     *
     * @throws GeometryEngineException
     */
    private function queryGeometry(string $operation, array $arguments, string $geometryClass): Geometry
    {
        $output = $this->query($operation, $arguments, 'wkt');

        try {
            return $geometryClass::fromText($output);
        } catch (GeometryException $e) {
            throw new GeometryEngineException('Failed to parse geosop output as geometry: ' . $output, $e);
        }
    }

    /**
     * @param list<Geometry|string|float> $arguments
     *
     * @throws GeometryEngineException
     */
    private function queryBoolean(string $operation, array $arguments): bool
    {
        $output = $this->query($operation, $arguments, 'txt');

        return match ($output) {
            'true' => true,
            'false' => false,
            default => throw new GeometryEngineException(sprintf(
                'Unexpected geosop output: expected boolean "true" or "false", got "%s"',
                $output,
            )),
        };
    }

    /**
     * @param list<Geometry|string|float> $arguments
     *
     * @throws GeometryEngineException
     */
    private function queryFloat(string $operation, array $arguments): float
    {
        $output = $this->query($operation, $arguments, 'txt');

        if (is_numeric($output)) {
            return (float) $output;
        }

        throw new GeometryEngineException(sprintf(
            'Unexpected geosop output: expected float, got "%s"',
            $output,
        ));
    }
}
