<?php

namespace Paknahad\Querifier;

class Operators
{
    const OP_EQUAL = '_eq';
    const OP_GREATER_THAN = '_gt';
    const OP_LESS_THAN = '_lt';
    const OP_NOT_EQUAL = '_not_eq';
    const OP_IS_NULL = '_null';
    const OP_IS_NOT_NULL = '_not_null';
    const OP_IN = '_in';
    const OP_NOT_IN = '_not_in';
    const OP_LIKE = '_like';
    const OP_NOT_LIKE = '_not_like';

    const OPERATORS = [
        self::OP_EQUAL => [
            'sql' => '=',
            'doctrine' => 'eq',
        ],
        self::OP_GREATER_THAN => [
            'sql' => '>',
            'doctrine' => 'gt',
        ],
        self::OP_LESS_THAN => [
            'sql' => '<',
            'doctrine' => 'lt',
        ],
        self::OP_NOT_EQUAL => [
            'sql' => '<>',
            'doctrine' => 'neq',
        ],
        self::OP_IS_NULL => [
            'sql' => 'IS NULL',
            'doctrine' => 'isNull',
        ],
        self::OP_IS_NOT_NULL => [
            'sql' => 'IS NOT NULL',
            'doctrine' => 'isNotNull',
        ],
        self::OP_IN => [
            'sql' => 'IN',
            'doctrine' => 'in',
        ],
        self::OP_NOT_IN => [
            'sql' => 'NOT IN',
            'doctrine' => 'notIn',
        ],
        self::OP_LIKE => [
            'sql' => 'LIKE',
            'doctrine' => 'like',
        ],
        self::OP_NOT_LIKE => [
            'sql' => 'NOT LIKE',
            'doctrine' => 'notLike',
        ],
    ];

    public static function getOperator(string $operator, string $queryType): string
    {
        return self::OPERATORS[$operator][$queryType];
    }
}
