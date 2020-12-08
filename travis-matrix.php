<?php

declare(strict_types=1);

$phpVersions = [
    '7.2',
    '7.3',
    '7.4',
    '8.0',
];

$engines = [
    'PDO_MYSQL',
    'PDO_MYSQL_MARIADB',
    'PDO_PGSQL',
    'SQLite3',
    'GEOS',
];

$latestDist = 'focal';

/**
 * The dists required to run a given PHP version or engine.
 * In chronological order. The latest compatible dist will be chosen.
 */
$requires = [
    '7.2' => ['trusty', 'xenial', 'bionic'],
    '7.3' => ['trusty', 'xenial', 'bionic'],
    '7.4' => ['trusty', 'xenial', 'bionic'],
    '8.0' => ['xenial', 'bionic', 'focal'],
];

/** @var Job[] $jobs */
$jobs = [];

foreach ($phpVersions as $phpVersion) {
    foreach ($engines as $engine) {
        $job = new Job();

        $dist = getDist($phpVersion, $engine);

        if ($dist === null) {
            continue;
        }

        $job->phpVersion = $phpVersion;
        $job->engine = $engine;
        $job->dist = $dist;

        $jobs[] = $job;
    }
}

echo "matrix:\n";
echo "  include:\n";

foreach ($jobs as $job) {
    echo "    - php: {$job->phpVersion}\n";
    echo "      env: ENGINE={$job->engine}\n";
    echo "      dist: {$job->dist}\n";
}

function getDist(string $phpVersion, string $engine): ?string
{
    global $latestDist;
    global $requires;

    if ($phpVersion === '8.0' && $engine === 'GEOS') {
        // GEOS PHP does not support PHP 8 yet
        // See: https://git.osgeo.org/gitea/geos/php-geos/issues/26
        return null;
    }

    $requiredDists = [];

    foreach ([$phpVersion, $engine] as $key) {
        if (isset($requires[$key])) {
            $requiredDists[] = (array) $requires[$key];
        }
    }

    switch (count($requiredDists)) {
        case 0:
            return $latestDist;

        case 1:
            return $requiredDists[0][count($requiredDists[0]) - 1];

        default:
            $dists = array_values(array_intersect($requiredDists[0], $requiredDists[1]));

            if (! $dists) {
                // conflicting requirements; can't run!
                return null;
            }

            return $dists[count($dists) - 1];
    }
}

class Job {
    /** @var string */
    public $phpVersion;

    /** @var string */
    public $engine;

    /** @var string */
    public $dist;
}
