<?php

namespace Paknahad\Querifier\Parts;

abstract class AbstractCondition implements ConditionInterface
{
    protected $name;
    protected $operator;

    abstract public function setName(?string $name): void;

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
