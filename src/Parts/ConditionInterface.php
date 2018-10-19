<?php
/**
 * Created by PhpStorm.
 * User: hamid
 * Date: 10/19/18
 * Time: 1:46 PM
 */

namespace Paknahad\QueryParser\Parts;


interface ConditionInterface
{
    public function getName(): string;

    public function getOperator(): string;
}
