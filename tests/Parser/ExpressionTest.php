<?php

namespace Paknahad\Querifier\Tests\Parser;

use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parser;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    /** @dataProvider provideFilterArray */
    public function testQuery(string $filterForExpression, $result)
    {
        $expressionParser = Parser\Expression::parseFromString($filterForExpression, null);

        $expressionConditions = $expressionParser->getQuery()->getConditions();
        $this->assertCount(count($result), $expressionConditions);

        foreach ($result as $expectedCombiner) {
            /** @var Combiner $expressionCombination */
            $expressionCombination = array_shift($expressionConditions);
            $this->assertSame($expectedCombiner['operator'], $expressionCombination->getOperator());
            $this->assertSame($expectedCombiner['conditionsName'], $expressionCombination->getConditionsName());
            $this->assertEquals($expectedCombiner['conditions'], $expressionCombination->getConditions());
        }
    }

    public function provideFilterArray()
    {
        yield [
            'name:%test|book.title<>%test',
            [
                [
                    'operator' => 'or',
                    'conditionsName' => ['___condition___1', '___condition___2'],
                    'conditions' => [
                        '___condition___1' => new Condition('name', Operators::OP_LIKE, '%test', '___condition___1'),
                        '___condition___2' => new Condition('book.title', Operators::OP_NOT_LIKE, '%test', '___condition___2'),
                    ],
                ],
            ],
        ];

        yield[
            '(name:%test|book.title<>%test)^book.id:[2,3]',
            [
                [
                    'operator' => 'and',
                    'conditionsName' => ['___combination___5', '___condition___5'],
                    'conditions' => [
                        '___condition___5' => new Condition('book.id', Operators::OP_IN, '2,3', '___condition___5'),
                        '___combination___5' => (
                        (new Combiner('or', ['___condition___3', '___condition___4'], '___combination___5'))
                            ->addCondition(new Condition('name', Operators::OP_LIKE, '%test', '___condition___3'))
                            ->addCondition(new Condition('book.title', Operators::OP_NOT_LIKE, '%test', '___condition___4'))
                        ),
                    ],
                ],
            ],
        ];
    }
}
