<?php

namespace Paknahad\Querifier\Parts;

abstract class AbstractCondition implements ConditionInterface
{
    protected $name;
    protected $operator;

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
