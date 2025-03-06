<?php

declare(strict_types=1);

namespace Brick\Geo\Attribute;

use Attribute;

/**
 * Used to tag a method that should not be proxied in Proxy classes.
 *
 * @internal
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class NoProxy
{
}
