<?php
/**
 * Created by PhpStorm.
 * User: hamid
 * Date: 10/19/18
 * Time: 1:46 PM
 */

namespace Paknahad\Querifier\Parts;


interface ConditionInterface
{
    public function getName(): string;

    public function getOperator(): string;
}
