<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;

/**
 * Well-Known Text parser.
 */
class WKTParser
{
    const T_WORD   = 1;
    const T_NUMBER = 2;

    const REGEX_WORD       = '([a-zA-Z]+)';
    const REGEX_NUMBER     = '(\-?[0-9]+(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?)';
    const REGEX_OTHER      = '(.)';
    const REGEX_WHITESPACE = '\s+';

    /**
     * Array of all regex. The order is important!
     *
     * @var array
     */
    private static $regex = [
        self::REGEX_WORD,
        self::REGEX_NUMBER,
        self::REGEX_WHITESPACE,
        self::REGEX_OTHER
    ];

    /**
     * An array of all tokens.
     *
     * @var array
     */
    private $tokens = [];

    /**
     * The current token.
     *
     * @var array
     */
    private $currentToken;

    /**
     * Class constructor.
     *
     * @param string $wkt
     */
    public function __construct($wkt)
    {
        $this->scan($wkt);
        $this->currentToken = current($this->tokens);
    }

    /**
     * @param string $wkt
     *
     * @return void
     */
    private function scan($wkt)
    {
        $regex = '/' . implode('|', self::$regex) . '/';

        preg_match_all($regex, $wkt, $matches);

        foreach ($matches as $type => $values) {
            if ($type === 0) {
                continue;
            }
            foreach ($values as $index => $value) {
                if ($value !== '') {
                    $this->tokens[$index] = [$type, $value];
                }
            }
        }

        ksort($this->tokens);
        reset($this->tokens);
    }

    /**
     * @return array|false The next token, or false if there are no more tokens.
     */
    private function nextToken()
    {
        $token = current($this->tokens);

        if ($token !== false) {
            next($this->tokens);
        }

        return $token;
    }

    /**
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function matchOpener()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected '(' but encountered end of stream");
        }
        if ($token[1] !== '(') {
            throw new GeometryException("Expected '(' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function matchCloser()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected ')' but encountered end of stream");
        }
        if ($token[1] !== ')') {
            throw new GeometryException("Expected ')' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function getNextWord()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected word but encountered end of stream");
        }
        if ($token[0] !== self::T_WORD) {
            throw new GeometryException("Expected word but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string|null The next word, or NULL if the next token is not a word, or there are no more tokens.
     */
    public function getOptionalNextWord()
    {
        $token = current($this->tokens);

        if ($token === false) {
            return null;
        }
        if ($token[0] !== self::T_WORD) {
            return null;
        }

        next($this->tokens);

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function getNextNumber()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected number but encountered end of stream");
        }
        if ($token[0] !== self::T_NUMBER) {
            throw new GeometryException("Expected number but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function getNextCloserOrComma()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected ')' or ',' but encountered end of stream");
        }
        if ($token[1] !== ')' && $token[1] !== ',') {
            throw new GeometryException("Expected ')' or ',' but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return boolean
     */
    public function isEndOfStream()
    {
        return $this->nextToken() === false;
    }
}
