<?php

namespace Paknahad\Querifier\Tests;

use Paknahad\Querifier\Factory;
use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testMakeCondition()
    {
        $condition = Factory::makeCondition('fieldName', [Operators::OP_LIKE => '%value'], 'test_make_condition');

        $this->assertTrue($condition instanceof Condition);
        $this->assertSame('test_make_condition', $condition->getName());
        $this->assertSame(Operators::OP_LIKE, $condition->getOperator());
        $this->assertSame('%value', $condition->getValue());
    }

    public function testMakeConditionWithDefaultOperatorAndDefaultName()
    {
        $condition = Factory::makeCondition('fieldName', 'value');

        $this->assertTrue($condition instanceof Condition);
        $this->assertSame(0, strpos($condition->getName(), '___condition___'));
        $this->assertSame(Operators::OP_EQUAL, $condition->getOperator());
        $this->assertSame('value', $condition->getValue());
    }

    public function testMakeCombiner()
    {
        $condition = Factory::makeCombiner(Combiner::AND, 'cond_1,cond_2', 'test_make_condition');

        $this->assertTrue($condition instanceof Combiner);
        $this->assertSame('test_make_condition', $condition->getName());
        $this->assertSame(Combiner::AND, $condition->getOperator());
        $this->assertSame(['cond_1', 'cond_2'], $condition->getConditionsName());
    }

    public function testMakeCombinerWithDefaultName()
    {
        $condition = Factory::makeCombiner(Combiner::OR, 'cond_1,cond_2');

        $this->assertTrue($condition instanceof Combiner);
        $this->assertSame(0, strpos($condition->getName(), '___combination___'));
        $this->assertSame(Combiner::OR, $condition->getOperator());
        $this->assertSame(['cond_1', 'cond_2'], $condition->getConditionsName());
    }

    /**
     * @expectedException Paknahad\Querifier\Exception\InvalidFilter
     */
    public function testInvalidFilter()
    {
        Factory::makeCombiner(Combiner::AND, 'cond_1', 'test_make_condition');
    }
}
