<?php
namespace Paknahad\Querifier\Parts;


abstract class AbstractCondition implements ConditionInterface
{
    protected $name;
    protected $operator;

    public function setName(?string $name): void
    {
        static $increment;
        $this->name = $name ?? '___condition___'.++$increment;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
