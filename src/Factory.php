<?php
namespace Paknahad\QueryParser;

use Paknahad\QueryParser\Exception\InvalidFilter;
use Paknahad\QueryParser\Parts\Combiner;
use Paknahad\QueryParser\Parts\Condition;

class Factory
{
    public static function makeCondition(string $field, $value, ?string $name = null): Condition
    {
        $operator = Operators::OP_EQUAL;

        if (is_array($value)) {
            reset($value);
            $operator = key($value);
            $value = $value[$operator];
        }

        return new Condition($field, $operator, $value, $name);
    }

    public static function makeCombiner(string $operator, string $value, string $name = null): Combiner
    {
        $conditions = explode(',', $value);

        if (count($conditions) < 2) {
            throw new InvalidFilter();
        }

        return new Combiner($operator, $conditions, $name);
    }
}
