<?php
namespace Paknahad\QueryParser\Parts;

use Paknahad\QueryParser\Exception\InvalidFilter;

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

    public function __construct(string $operator, array $conditionsName, ?string $name = null)
    {
        if (!in_array($operator, $this->validOperators)) {
            throw new InvalidFilter('Invalid Operator');
        }

        $this->operator = $operator;
        $this->conditionsName = $conditionsName;
        $this->setName($name);
    }

    public function getConditionsName(): array
    {
        return $this->conditionsName;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function addCondition(AbstractCondition $condition): void
    {
        $this->conditions[$condition->getName()] = $condition;
    }
}
