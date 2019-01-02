<?php

namespace Paknahad\Querifier\Tests;

use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parser;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @dataProvider provideFilterArray */
    public function testQuery(array $filter, $result)
    {
        $parser = Parser::parsFromArray($filter, []);

        $query = $parser->getQuery();
        $condisions = $query->getConditions();
        $this->assertCount(count($result), $condisions);

        foreach ($result as $expectedCombiner) {
            /** @var Combiner $combination */
            $combination = array_shift($condisions);
            $this->assertSame($expectedCombiner['operator'], $combination->getOperator());
            $this->assertSame($expectedCombiner['conditionsName'], $combination->getConditionsName());
            $this->assertEquals($expectedCombiner['conditions'], $combination->getConditions());
        }
    }

    public function provideFilterArray()
    {
        yield[
            [
                '_c1' => ['name' => [Operators::OP_LIKE => '%test']],
                '_c2' => ['book.title' => [Operators::OP_NOT_LIKE => '%test']],
                '_cmb_or' => '_c1,_c2',
            ],
            [
                [
                    'operator' => 'or',
                    'conditionsName' => ['_c1', '_c2'],
                    'conditions' => [
                        '_c1' => new Condition('name', Operators::OP_LIKE, '%test', '_c1'),
                        '_c2' => new Condition('book.title', Operators::OP_NOT_LIKE, '%test', '_c2'),
                    ],
                ],
            ],
        ];
        yield[
            [
                '_c1' => ['name' => [Operators::OP_LIKE => '%test']],
                '_c2' => ['book.title' => [Operators::OP_NOT_LIKE => '%test']],
                '_c3' => ['_cmb_or' => '_c1,_c2'],
                '_c4' => ['book.id' => [Operators::OP_IN => '2,3']],
                '_cmb_and' => '_c3,_c4',
            ],
            [
                [
                    'operator' => 'and',
                    'conditionsName' => ['_c3', '_c4'],
                    'conditions' => [
                        '_c4' => new Condition('book.id', Operators::OP_IN, '2,3', '_c4'),
                        '_c3' => (
                            (new Combiner('or', ['_c1', '_c2'], '_c3'))
                                ->addCondition(new Condition('name', Operators::OP_LIKE, '%test', '_c1'))
                                ->addCondition(new Condition('book.title', Operators::OP_NOT_LIKE, '%test', '_c2'))
                        ),
                    ],
                ],
            ],
        ];
        yield[
            [
                '_c1' => ['name' => [Operators::OP_LIKE => '%test']],
                '_c2' => ['book.title' => [Operators::OP_NOT_LIKE => '%test']],
                '_c3' => ['_cmb_or' => '_c1,_c2'],
                '_c4' => ['book.id' => [Operators::OP_IN => '2,3']],
                '_cmb_or' => '_c1,_c4',
            ],
            [
                [
                    'operator' => 'or',
                    'conditionsName' => ['_c1', '_c2'],
                    'conditions' => [
                        '_c1' => new Condition('name', Operators::OP_LIKE, '%test', '_c1'),
                        '_c2' => new Condition('book.title', Operators::OP_NOT_LIKE, '%test', '_c2'),
                    ],
                ],
                [
                    'operator' => 'or',
                    'conditionsName' => ['_c1', '_c4'],
                    'conditions' => [
                        '_c1' => new Condition('name', Operators::OP_LIKE, '%test', '_c1'),
                        '_c4' => new Condition('book.id', Operators::OP_IN, '2,3', '_c4'),
                    ],
                ],
            ],
        ];
    }
}
