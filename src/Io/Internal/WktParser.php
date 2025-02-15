<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

use Brick\Geo\Exception\GeometryIoException;

/**
 * Well-Known Text parser, with support for EWKT.
 *
 * @internal
 */
final class WktParser
{
    private const string REGEX_CAPTURE_WORD   = '([a-z]+)';
    private const string REGEX_CAPTURE_NUMBER = '(\-?[0-9]+(?:\.[0-9]+)?(?:e[\+\-]?[0-9]+)?)';
    private const string REGEX_WHITESPACE = '\s+';
    private const string REGEX_CAPTURE_OTHER = '(.+?)';
    private const string REGEX_CAPTURE_SRID = 'SRID\=([0-9]+)\s*;'; // EWKT

    /**
     * The list of tokens.
     *
     * The first element of each token is the token type, the second element is the token value.
     *
     * @var list<array{WktTokenType, string}>
     */
    private array $tokens = [];

    /**
     * The current token pointer.
     */
    private int $current = 0;

    public function __construct(
        string $wkt,
        private bool $ewkt = false,
    ) {
        $this->scan($wkt);
    }

    private function scan(string $wkt) : void
    {
        $regexPatterns = [
            self::REGEX_CAPTURE_WORD,
            self::REGEX_CAPTURE_NUMBER,
            self::REGEX_WHITESPACE,
            self::REGEX_CAPTURE_OTHER,
        ];

        if ($this->ewkt) {
            array_unshift($regexPatterns, self::REGEX_CAPTURE_SRID);
        }

        $matchKeyToType = $this->ewkt ? [
            1 => WktTokenType::Srid,
            2 => WktTokenType::Word,
            3 => WktTokenType::Number,
            4 => WktTokenType::Other,
        ] : [
            1 => WktTokenType::Word,
            2 => WktTokenType::Number,
            3 => WktTokenType::Other,
        ];

        $regex = '/' . implode('|', $regexPatterns) . '/i';

        preg_match_all($regex, $wkt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            foreach ($match as $key => $value) {
                /** @var int $key */

                if ($key === 0) {
                    continue;
                }

                if ($value !== '') {
                    $this->tokens[] = [$matchKeyToType[$key], $value];
                }
            }
        }
    }

    /**
     * @return array{WktTokenType, string}|null The next token, or null if there are no more tokens.
     */
    private function peekToken(): ?array
    {
        return $this->tokens[$this->current] ?? null;
    }

    /**
     * @return array{WktTokenType, string}|null The next token, or null if there are no more tokens.
     */
    private function nextToken() : ?array
    {
        $token = $this->peekToken();

        if ($token === null) {
            return null;
        }

        $this->current++;

        return $token;
    }

    /**
     * @throws GeometryIoException
     */
    public function matchOpener() : void
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new GeometryIoException("Expected '(' but encountered end of stream");
        }
        if ($token[1] !== '(') {
            throw new GeometryIoException("Expected '(' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @throws GeometryIoException
     */
    public function matchCloser() : void
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new GeometryIoException("Expected ')' but encountered end of stream");
        }
        if ($token[1] !== ')') {
            throw new GeometryIoException("Expected ')' but encountered '" . $token[1] . "'");
        }
    }

    /**
     * @throws GeometryIoException
     */
    public function getNextWord() : string
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new GeometryIoException("Expected word but encountered end of stream");
        }
        if ($token[0] !== WktTokenType::Word) {
            throw new GeometryIoException("Expected word but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    /**
     * @return string|null The next word, or NULL if the next token is not a word, or there are no more tokens.
     */
    public function getOptionalNextWord() : ?string
    {
        $token = $this->tokens[$this->current] ?? null;

        if ($token === null) {
            return null;
        }

        if ($token[0] !== WktTokenType::Word) {
            return null;
        }

        $this->current++;

        return $token[1];
    }

    public function matchOptionalOpener(): bool
    {
        $token = $this->peekToken();

        $isOpener = ($token !== null && $token[1] === '(');

        if ($isOpener) {
            $this->current++;
        }

        return $isOpener;
    }

    /**
     * Returns whether the next token is an opener or a word.
     *
     * @return bool True if the next token is an opener, false if it is a word.
     *
     * @throws GeometryIoException If the next token is not an opener or a word, or if there is no next token.
     */
    public function isNextOpenerOrWord() : bool
    {
        $token = $this->tokens[$this->current] ?? null;

        if ($token === null) {
            throw new GeometryIoException("Expected '(' or word but encountered end of stream");
        }

        if ($token[1] === '(') {
            return true;
        }

        if ($token[0] === WktTokenType::Word) {
            return false;
        }

        throw new GeometryIoException("Expected '(' or word but encountered '" . $token[1] . "'");
    }

    /**
     * @throws GeometryIoException
     */
    public function getNextNumber() : float
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new GeometryIoException("Expected number but encountered end of stream");
        }

        if ($token[0] !== WktTokenType::Number) {
            throw new GeometryIoException("Expected number but encountered '" . $token[1] . "'");
        }

        return (float) $token[1];
    }

    /**
     * @throws GeometryIoException
     */
    public function getNextCloserOrComma() : string
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new GeometryIoException("Expected ')' or ',' but encountered end of stream");
        }
        if ($token[1] !== ')' && $token[1] !== ',') {
            throw new GeometryIoException("Expected ')' or ',' but encountered '" . $token[1] . "'");
        }

        return $token[1];
    }

    public function getOptionalSrid() : int
    {
        $token = $this->tokens[$this->current] ?? null;

        if ($token === null) {
            return 0;
        }

        if ($token[0] !== WktTokenType::Srid) {
            return 0;
        }

        $this->current++;

        return (int) $token[1];
    }

    public function isEndOfStream() : bool
    {
        return $this->nextToken() === null;
    }
}
