<?php

namespace Brick\Geo\IO;

/**
 * Parser for the Extended WKT format designed by PostGIS.
 */
class EWKTParser extends WKTParser
{
    const T_SRID = 1;
    const T_WORD = 2;
    const T_NUMBER = 3;

    const REGEX_SRID = 'SRID\=([0-9]+)\s*;';

    /**
     * {@inheritdoc}
     */
    protected function getRegex() : array
    {
        return [
            self::T_SRID   => self::REGEX_SRID,
            self::T_WORD   => self::REGEX_WORD,
            self::T_NUMBER => self::REGEX_NUMBER,
        ];
    }

    /**
     * @return int
     */
    public function getOptionalSRID() : int
    {
        $token = current($this->tokens);

        if ($token === false) {
            return 0;
        }
        if ($token[0] !== self::T_SRID) {
            return 0;
        }

        next($this->tokens);

        return (int) $token[1];
    }
}
