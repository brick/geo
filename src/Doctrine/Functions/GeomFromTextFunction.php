<?php

namespace Brick\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * GeomFromText() function for geometries.
 */
class GeomFromTextFunction extends FunctionNode
{
    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $wkt;

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'ST_GeomFromText(%s, 4326)',
            $this->wkt->dispatch($sqlWalker)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->wkt = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
