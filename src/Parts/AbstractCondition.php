<?php

namespace Paknahad\Querifier\Parts;

abstract class AbstractCondition implements ConditionInterface
{
    protected $name;
    protected $operator;

    /**
     * @param string|null $name
     */
    abstract public function setName(?string $name): void;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }
}
