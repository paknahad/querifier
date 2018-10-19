<?php
namespace Paknahad\QueryParser\Parts;

use Paknahad\QueryParser\Exception\InvalidOperator;
use Paknahad\QueryParser\Operators;

class Condition extends AbstractCondition
{
    private $field;
    private $value;

    public function __construct(string $field, string $operator, string $value, ?string $name = null)
    {
        if (!in_array($operator, array_keys(Operators::OPERATORS))) {
            throw new InvalidOperator();
        }

        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->setName($name);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
