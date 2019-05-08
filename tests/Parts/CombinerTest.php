<?php

namespace Paknahad\Querifier\Tests\Parts;

use Paknahad\Querifier\Parts\Combiner;
use PHPUnit\Framework\TestCase;

class CombinerTest extends TestCase
{
    /**
     * @expectedException Paknahad\Querifier\Exception\InvalidOperator
     */
    public function testInvalidFilter()
    {
        new Combiner('invalid', ['condition1', 'condition2']);
    }

    public function testSetName()
    {
        $combiner = new Combiner(Combiner::AND, ['condition1', 'condition2']);

        $combiner->setName(null);
        $this->assertSame(0, strpos($combiner->getName(), '___combination___'));

        $combiner->setName('test');
        $this->assertEquals('test', $combiner->getName());
    }
}
