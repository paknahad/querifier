<?php

namespace Paknahad\Querifier\Tests\Parts;

use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    /**
     * @expectedException Paknahad\Querifier\Exception\InvalidOperator
     */
    public function testInvalidFilter()
    {
        new Condition('field', 'invalid', 'value');
    }

    public function testSetName()
    {
        $combiner = new Condition('field', Operators::OP_EQUAL, 'value');

        $combiner->setName(null);
        $this->assertSame(0, strpos($combiner->getName(), '___condition___'));

        $combiner->setName('test');
        $this->assertEquals('test', $combiner->getName());
    }
}
