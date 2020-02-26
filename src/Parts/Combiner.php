<?php

namespace Paknahad\Querifier\Parts;

use Paknahad\Querifier\Exception\InvalidFilter;
use Paknahad\Querifier\Exception\InvalidOperator;

class Combiner extends AbstractCondition
{
    const OR = 'or';
    const AND = 'and';

    protected $validOperators = [
        self::OR,
        self::AND,
    ];

    protected $conditions;
    protected $conditionsName;

    /**
     * Combiner constructor.
     *
     * @throws InvalidFilter
     */
    public function __construct(string $operator, array $conditionsName, ?string $name = null)
    {
        if (!\in_array($operator, $this->validOperators)) {
            throw new InvalidOperator('Invalid Operator: '.$operator);
        }

        $this->operator = $operator;
        $this->conditionsName = $conditionsName;
        $this->setName($name);
    }

    public function setName(?string $name): void
    {
        static $increment;
        $this->name = $name ?? '___combination___'.++$increment;
    }

    public function getConditionsName(): array
    {
        return $this->conditionsName;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return Combiner
     */
    public function addCondition(AbstractCondition $condition): self
    {
        $this->conditions[$condition->getName()] = $condition;

        return $this;
    }
}
