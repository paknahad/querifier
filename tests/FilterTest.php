<?php

namespace Paknahad\Querifier\Tests;

use Paknahad\Querifier\Filter;
use Paknahad\Querifier\Parser\Criteria;
use Paknahad\Querifier\Parser\Expression;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

class FilterTest extends TestCase
{
    /**
     * @expectedException Paknahad\Querifier\Exception\InvalidQuery
     */
    public function testInvalidQuery()
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $filter = new Filter($requestMock);

        $filter->applyFilter($filter);
    }

    /** @dataProvider provideFilter */
    public function testParserChoose($hasParameterQ, $parserClass)
    {
        $parserReflection = self::getMethod('getParser');

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getAttribute')->willReturn($hasParameterQ);

        $filter = new Filter($requestMock);

        $this->assertSame($parserClass, get_class($parserReflection->invokeArgs($filter, [$requestMock])));
    }

    public function provideFilter()
    {
        yield [
            true,
            Expression::class,
        ];

        yield [
            false,
            Criteria::class,
        ];
    }

    private static function getMethod($name)
    {
        $class = new ReflectionClass(Filter::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
