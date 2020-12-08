<?php

declare(strict_types=1);

$phpVersions = [
    '7.2',
    '7.3',
    '7.4',
    '8.0',
];

$engines = [
    'PDO_MYSQL56',
    'PDO_MYSQL57',
    'PDO_MYSQL80',
    'PDO_MYSQL_MARIADB55',
    'PDO_MYSQL_MARIADB10',
    'PDO_PGSQL',
    'SQLite3',
    'GEOS',
];

$defaultDist = 'trusty';

$requires = [
    'PDO_MYSQL56'         => 'trusty',
    'PDO_MYSQL57'         => 'xenial',
    'PDO_MYSQL80'         => 'xenial',
    'PDO_MYSQL_MARIADB55' => 'trusty',
    'PDO_PGSQL'           => 'xenial',
    'SQLite3'             => 'xenial',
    '8.0'                 => 'xenial',
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
    global $defaultDist;
    global $requires;

    if ($phpVersion === '8.0' && substr($engine, 0, 4) === 'GEOS') {
        // GEOS PHP does not support PHP 8 yet
        // See: https://git.osgeo.org/gitea/geos/php-geos/issues/26
        return null;
    }

    $requiredDists = [];

    foreach ([$phpVersion, $engine] as $key) {
        if (isset($requires[$key])) {
            $requiredDists[] = $requires[$key];
        }
    }

    $requiredDists = array_values(array_unique($requiredDists));

    switch (count($requiredDists)) {
        case 0:
            return $defaultDist;

        case 1:
            return $requiredDists[0];

        default:
            // conflicting requirements, for example PDO_MYSQL56 requires trusty, but PHP 8.0 requires xenial;
            // can't run!
            return null;
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
