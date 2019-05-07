<?php

namespace Paknahad\Querifier\Parts;

use Paknahad\Querifier\Exception\InvalidFilter;

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
     * @param string      $operator
     * @param array       $conditionsName
     * @param string|null $name
     *
     * @throws InvalidFilter
     */
    public function __construct(string $operator, array $conditionsName, ?string $name = null)
    {
        if (!\in_array($operator, $this->validOperators)) {
            throw new InvalidFilter('Invalid Operator');
        }

        $this->operator = $operator;
        $this->conditionsName = $conditionsName;
        $this->setName($name);
    }

    /**
     * @return array
     */
    public function getConditionsName(): array
    {
        return $this->conditionsName;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param AbstractCondition $condition
     *
     * @return Combiner
     */
    public function addCondition(AbstractCondition $condition): self
    {
        $this->conditions[$condition->getName()] = $condition;

        return $this;
    }
}
