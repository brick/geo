<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiLineStringProxy;

/**
 * Doctrine type for MultiLineString.
 */
class MultiLineStringType extends GeometryType
{
    public function getName()
    {
        return 'MultiLineString';
    }

    protected function getProxyClassName() : string
    {
        return MultiLineStringProxy::class;
    }

    protected function hasKnownSubclasses() : bool
    {
        return false;
    }
}
