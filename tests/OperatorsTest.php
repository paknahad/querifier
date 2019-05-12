<?php

namespace Paknahad\Querifier\Tests;

use Paknahad\Querifier\Operators;
use PHPUnit\Framework\TestCase;

class OperatorsTest extends TestCase
{
    public function testMakeCondition()
    {
        $this->assertSame('eq', Operators::getOperator(Operators::OP_EQUAL, 'doctrine'));
        $this->assertSame('=', Operators::getOperator(Operators::OP_EQUAL, 'sql'));

        $this->assertSame('gt', Operators::getOperator(Operators::OP_GREATER_THAN, 'doctrine'));
        $this->assertSame('>', Operators::getOperator(Operators::OP_GREATER_THAN, 'sql'));

        $this->assertSame('lt', Operators::getOperator(Operators::OP_LESS_THAN, 'doctrine'));
        $this->assertSame('<', Operators::getOperator(Operators::OP_LESS_THAN, 'sql'));

        $this->assertSame('neq', Operators::getOperator(Operators::OP_NOT_EQUAL, 'doctrine'));
        $this->assertSame('<>', Operators::getOperator(Operators::OP_NOT_EQUAL, 'sql'));

        $this->assertSame('isNull', Operators::getOperator(Operators::OP_IS_NULL, 'doctrine'));
        $this->assertSame('IS NULL', Operators::getOperator(Operators::OP_IS_NULL, 'sql'));

        $this->assertSame('isNotNull', Operators::getOperator(Operators::OP_IS_NOT_NULL, 'doctrine'));
        $this->assertSame('IS NOT NULL', Operators::getOperator(Operators::OP_IS_NOT_NULL, 'sql'));

        $this->assertSame('in', Operators::getOperator(Operators::OP_IN, 'doctrine'));
        $this->assertSame('IN', Operators::getOperator(Operators::OP_IN, 'sql'));

        $this->assertSame('notIn', Operators::getOperator(Operators::OP_NOT_IN, 'doctrine'));
        $this->assertSame('NOT IN', Operators::getOperator(Operators::OP_NOT_IN, 'sql'));

        $this->assertSame('like', Operators::getOperator(Operators::OP_LIKE, 'doctrine'));
        $this->assertSame('LIKE', Operators::getOperator(Operators::OP_LIKE, 'sql'));

        $this->assertSame('notLike', Operators::getOperator(Operators::OP_NOT_LIKE, 'doctrine'));
        $this->assertSame('NOT LIKE', Operators::getOperator(Operators::OP_NOT_LIKE, 'sql'));
    }
}
