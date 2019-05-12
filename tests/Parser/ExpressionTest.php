<?php

namespace Paknahad\Querifier\Tests\Parser;

use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parser;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use Paknahad\Querifier\Tests\TestUtils\ConditionsAssertionTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ExpressionTest extends TestCase
{
    use ConditionsAssertionTrait;

    public function testSorting()
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $requestMock->method('getQueryParams')->willReturn(['sort' => 'name,-book.title']);
        $parser = Parser\Expression::parseFromPsrRequest($requestMock);

        $this->assertSame(
            [
                [
                    'field' => 'name',
                    'direction' => 'ASC',
                ],
                [
                    'field' => 'book.title',
                    'direction' => 'DESC',
                ],
            ],
            $parser->getSorting()
        );
    }

    /**
     * @expectedException Paknahad\Querifier\Exception\InvalidQueryString
     * @dataProvider provideInvalidQueryString
     */
    public function testInvalidFilter($filter)
    {
        Parser\Expression::parseFromString($filter, '');
    }

    public function provideInvalidQueryString()
    {
        yield [
            'name:%test|book.title=%test',
        ];

        yield [
            '(name:%test|book.title<>%test',
        ];

        yield [
            'name:%test|book.title<>%test)',
        ];
    }

    /** @dataProvider provideFilterArray */
    public function testQuery(string $filterForExpression, $result)
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $requestMock->method('getQueryParams')->willReturn(['q' => $filterForExpression]);
        $expressionParser = Parser\Expression::parseFromPsrRequest($requestMock);

        $expressionConditions = $expressionParser->getQuery()->getConditions();
        $this->assertCount(count($result), $expressionConditions);

        $this->assertSameConditions($result, $expressionConditions);
    }

    public function provideFilterArray()
    {
        yield [
            'name:%test|book.title<>%test',
            [
                (new Combiner(Combiner::OR, []))
                    ->addCondition(new Condition('name', Operators::OP_LIKE, '%test'))
                    ->addCondition(new Condition('book.title', Operators::OP_NOT_LIKE, '%test')),
            ],
        ];

        yield [
            'book.publish_date>2017|book.title:null',
            [
                (new Combiner(Combiner::OR, []))
                    ->addCondition(new Condition('book.publish_date', Operators::OP_GREATER_THAN, '2017'))
                    ->addCondition(new Condition('book.title', Operators::OP_IS_NULL, 'null')),
            ],
        ];

        yield[
            '(name:%test|book.title<>%test)^book.id:[2,3]',
            [
                (new Combiner(Combiner::AND, []))
                    ->addCondition(
                        (new Combiner(Combiner::OR, []))
                            ->addCondition(new Condition('name', Operators::OP_LIKE, '%test'))
                            ->addCondition(new Condition('book.title', Operators::OP_NOT_LIKE, '%test'))
                    )
                    ->addCondition(new Condition('book.id', Operators::OP_IN, '2,3')),
            ],
        ];
    }
}
