<?php

namespace Paknahad\Querifier\Tests\TestUtils;

use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;

trait ConditionsAssertionTrait
{
    protected function assertSameConditions(array $expectedConditions, array $conditions, $checkNames = false)
    {
        foreach ($expectedConditions as $expectedCondition) {
            $condition = array_shift($conditions);

            if ($checkNames) {
                $this->assertSame($expectedCondition->getName(), $condition->getName());
            }

            $this->assertSame($expectedCondition->getOperator(), $condition->getOperator());

            if ($expectedCondition instanceof Condition) {
                $this->assertTrue($condition instanceof Condition);
                $this->assertSame($expectedCondition->getValue(), $condition->getValue());
                $this->assertSame($expectedCondition->getField(), $condition->getField());
            } else {
                $this->assertTrue($condition instanceof Combiner);
                $this->assertSameConditions($expectedCondition->getConditions(), $condition->getConditions());
            }
        }
    }
}
