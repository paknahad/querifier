<?php

namespace Paknahad\Querifier\Parts;

use Paknahad\Querifier\Exception\InvalidOperator;
use Paknahad\Querifier\Operators;

class Condition extends AbstractCondition
{
    private $field;
    private $value;

    /**
     * Condition constructor.
     *
     * @param string      $field
     * @param string      $operator
     * @param string      $value
     * @param string|null $name
     *
     * @throws InvalidOperator
     */
    public function __construct(string $field, string $operator, string $value, ?string $name = null)
    {
        if (!\in_array($operator, array_keys(Operators::OPERATORS))) {
            throw new InvalidOperator('Invalid Operator');
        }

        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->setName($name);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        static $increment;
        $this->name = $name ?? '___condition___'.++$increment;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
