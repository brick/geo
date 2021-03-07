<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * Parser for the Extended WKT format designed by PostGIS.
 */
class EWKTParser extends WKTParser
{
    protected const T_SRID = 1;
    protected const T_WORD = 2;
    protected const T_NUMBER = 3;

    protected const REGEX_SRID = 'SRID\=([0-9]+)\s*;';

    protected function getRegex() : array
    {
        return [
            self::T_SRID   => self::REGEX_SRID,
            self::T_WORD   => self::REGEX_WORD,
            self::T_NUMBER => self::REGEX_NUMBER,
        ];
    }

    public function getOptionalSRID() : int
    {
        $token = $this->tokens[$this->current] ?? null;

        if ($token === null) {
            return 0;
        }

        if ($token[0] !== self::T_SRID) {
            return 0;
        }

        $this->current++;

        return (int) $token[1];
    }
}
