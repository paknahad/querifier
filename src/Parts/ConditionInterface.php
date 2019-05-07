<?php

namespace Paknahad\Querifier\Parts;

interface ConditionInterface
{
    public function getName(): string;

    public function getOperator(): string;
}
