<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

use Throwable;

/**
 * Base class for all geometry exceptions.
 *
 * This class is abstract to ensure that only fine-grained exceptions are thrown throughout the code.
 */
abstract class GeometryException extends \Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
