<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryParseException;

/**
 * Well-Known Text parser.
 */
class WKTParser
{
    const T_WORD   = 1;
    const T_NUMBER = 2;

    const REGEX_WORD   = '([a-z]+)';
    const REGEX_NUMBER = '(\-?[0-9]+(?:\.[0-9]+)?(?:e[\+\-]?[0-9]+)?)';

    /**
     * An array of all tokens.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Class constructor.
     *
     * @param string $wkt
     */
    public function __construct($wkt)
    {
        $this->scan($wkt);
    }

    /**
     * @return array
     */
    protected function getRegex()
    {
        return [
            self::T_WORD   => self::REGEX_WORD,
            self::T_NUMBER => self::REGEX_NUMBER,
        ];
    }

    /**
     * @param string $wkt
     *
     * @return void
     */
    private function scan($wkt)
    {
        $regex = $this->getRegex();
        $regex[] = '\s+';
        $regex[] = '(.+?)';

        $regex = '/' . implode('|', $regex) . '/i';

        preg_match_all($regex, $wkt, $matches, PREG_SET_ORDER);

        foreach ($matches as $index => $match) {
            foreach ($match as $key => $value) {
                if ($key === 0) {
                    continue;
                }

                if ($value !== '') {
                    $this->tokens[$index] = [$key, $value];
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
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function matchOpener()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryParseException("Expected '(' but encountered end of stream");
        }
        if ($token[1] !== '(') {
            throw new GeometryParseException("Expected '(' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function matchCloser()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryParseException("Expected ')' but encountered end of stream");
        }
        if ($token[1] !== ')') {
            throw new GeometryParseException("Expected ')' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function getNextWord()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryParseException("Expected word but encountered end of stream");
        }
        if ($token[0] !== static::T_WORD) {
            throw new GeometryParseException("Expected word but encountered '" . $token[1] . "'");
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
        if ($token[0] !== static::T_WORD) {
            return null;
        }

        next($this->tokens);

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function getNextNumber()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryParseException("Expected number but encountered end of stream");
        }
        if ($token[0] !== static::T_NUMBER) {
            throw new GeometryParseException("Expected number but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function getNextCloserOrComma()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryParseException("Expected ')' or ',' but encountered end of stream");
        }
        if ($token[1] !== ')' && $token[1] !== ',') {
            throw new GeometryParseException("Expected ')' or ',' but encountered '" . $token[1] . "'");
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
