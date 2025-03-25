<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Engine\Database\Internal\AbstractDatabaseWkbEngine;
use Override;

/**
 * Database engine based on MySQL.
 */
final readonly class MysqlEngine extends AbstractDatabaseWkbEngine
{
    #[Override]
    public function isRing(Curve $curve) : bool
    {
        // MySQL does not support ST_IsRing(), but we have an easy fallback.
        return $this->isClosed($curve) && $this->isSimple($curve);
    }

    public function getMysqlVersion(): string
    {
        return $this->driver->executeQuery('SELECT VERSION()')->get(0)->asString();
    }
}
