<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Engine\Database\Internal\AbstractDatabaseWkbEngine;

/**
 * Database engine based on MariaDB.
 */
final readonly class MariadbEngine extends AbstractDatabaseWkbEngine
{
    public function getMariadbVersion() : string
    {
        $version = $this->driver->executeQuery('SELECT VERSION()')->get(0)->asString();

        if (false !== $pos = strpos($version, '-MariaDB')) {
            return substr($version, 0, $pos);
        }

        return $version;
    }
}
