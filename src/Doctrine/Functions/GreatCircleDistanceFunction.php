<?php

namespace Brick\Geo\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * Great circle distance function.
 *
 * This function calculates the distance between two points on the Earth, approximated as a sphere.
 * It provides a fairly good precision (error < 0.5%) but is quite compute expensive.
 */
class GreatCircleDistanceFunction extends FunctionNode
{
    /**
     * The great-circle distance formula.
     *
     * @const string
     */
    const FORMULA = '(%s * ACOS(SIN(%s) * SIN(%s) + COS(%s) * COS(%s) * COS(%s - %s)))';

    /**
     * The average great-circle circumference of the Earth, in meters.
     *
     * @const float
     */
    const EARTH_RADIUS = 6371007.1809;

    /**
     * The number of radians in a degree.
     */
    const DEGREES_TO_RADIANS = 0.0174532925;

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $arg1;

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $arg2;

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $s)
    {
        $platform = $s->getConnection()->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'postgresql':
                return sprintf(
                    'ST_Distance_Sphere(%s, %s)',
                    $this->arg1->dispatch($s),
                    $this->arg2->dispatch($s)
                );

            case 'sqlite':
                return sprintf(
                    'GreatCircleLength(MakeLine(%s, %s))',
                    $this->arg1->dispatch($s),
                    $this->arg2->dispatch($s)
                );
        }

        return sprintf(
            self::FORMULA,
            self::EARTH_RADIUS,
            $this->y($this->arg1->dispatch($s)),
            $this->y($this->arg2->dispatch($s)),
            $this->y($this->arg1->dispatch($s)),
            $this->y($this->arg2->dispatch($s)),
            $this->x($this->arg2->dispatch($s)),
            $this->x($this->arg1->dispatch($s))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->arg1 = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->arg2 = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private static function x($value)
    {
        return sprintf('ST_X(%s) * %f', $value, self::DEGREES_TO_RADIANS);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function y($value)
    {
        return sprintf('ST_Y(%s) * %f', $value, self::DEGREES_TO_RADIANS);
    }
}
