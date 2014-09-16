<?php

namespace Brick\Geo\IO;

use Brick\Geo\GeometryException;

/**
 * Well-Known Text parser.
 */
class WktParser
{
    const T_EOF    = 0;
    const T_WORD   = 1;
    const T_NUMBER = 2;
    const T_OTHER  = 3;

    const REGEX_WORD       = '([a-zA-Z]+)';
    const REGEX_NUMBER     = '(\-?[0-9]+(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?)';
    const REGEX_OTHER      = '(.)';
    const REGEX_WHITESPACE = '\s+';

    const INDEX_TYPE  = 0;
    const INDEX_VALUE = 1;

    /**
     * Array of all regex. The order is important!
     *
     * @var array
     */
    protected static $regex = [
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
    protected $tokens = [];

    /**
     * The current token.
     *
     * @var array
     */
    protected $currentToken;

    /**
     * Class constructor.
     *
     * @param string $wkt
     */
    public function __construct($wkt)
    {
        $this->scan(strtoupper($wkt));
        $this->currentToken = current($this->tokens);
    }

    /**
     * @param string $wkt
     *
     * @return void
     */
    protected function scan($wkt)
    {
        $regex = '/' . implode('|', self::$regex) . '/';

        preg_match_all($regex, $wkt, $matches, PREG_PATTERN_ORDER);
        assert(count($matches) == count(self::$regex));

        foreach ($matches as $type => $values) {
            if ($type == 0) {
                continue;
            }
            foreach ($values as $index => $value) {
                if ($value != '') {
                    assert(! isset($this->tokens[$index]));
                    $this->tokens[$index] = [
                        self::INDEX_TYPE => $type,
                        self::INDEX_VALUE => $value
                    ];
                }
            }
        }

        ksort($this->tokens);
        reset($this->tokens);
    }

    /**
     * @return array
     */
    protected function nextToken()
    {
        $token = current($this->tokens);
        next($this->tokens);

        return $token;
    }

    /**
     * @return void
     *
     * @throws \Brick\Geo\GeometryException
     */
    public function matchOpener()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected '(' but encountered end of stream");
        }
        if ($token[1] != '(') {
            throw new GeometryException("Expected '(' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return void
     *
     * @throws \Brick\Geo\GeometryException
     */
    public function matchCloser()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected ')' but encountered end of stream");
        }
        if ($token[1] != ')') {
            throw new GeometryException("Expected ')' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\GeometryException
     */
    public function getNextWord()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected word but encountered end of stream");
        }
        if ($token[0] != self::T_WORD) {
            throw new GeometryException("Expected word but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\GeometryException
     */
    public function getNextNumber()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected number but encountered end of stream");
        }
        if ($token[0] != self::T_NUMBER) {
            throw new GeometryException("Expected number but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string
     *
     * @throws \Brick\Geo\GeometryException
     */
    public function getNextCloserOrComma()
    {
        $token = $this->nextToken();

        if ($token === false) {
            throw new GeometryException("Expected ')' or ',' but encountered end of stream");
        }
        if ($token[1] != ')' && $token[1] != ',') {
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

    //	/**
    //	 * Returns the type of the current token.
    //	 *
    //	 * @return integer T_EOF | T_WORD | T_NUMBER | T_OTHER
    //	 */
    //	protected function currentType()
    //	{
    //		if (is_array($this->currentToken)) {
    //			return $this->currentToken[self::INDEX_TYPE];
    //		}
    //		return self::T_EOF;
    //	}
    //
    //	/**
    //	 * Returns the value of the current token as a string,
    //	 * or null if EOF has been reached.
    //	 *
    //	 * @return string|null
    //	 */
    //	public function currentValue()
    //	{
    //		return is_array($this->currentToken)
    //		     ? $this->currentToken[self::INDEX_VALUE]
    //		     : null;
    //	}
    //
    //	public function moveNext()
    //	{
    //		$this->currentToken = next($this->tokens);
    //	}
    //
    //	public function isEof()
    //	{
    //		return $this->currentType() == self::T_EOF;
    //	}
    //
    //	public function isWord()
    //	{
    //		return $this->currentType() == self::T_WORD;
    //	}
    //
    //	public function isNumber()
    //	{
    //		return $this->currentType() == self::T_NUMBER;
    //	}
}
