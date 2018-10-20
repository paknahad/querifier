<?php
namespace Paknahad\Querifier\Filters;

use Paknahad\Querifier\Parts\AbstractCondition;
use Paknahad\Querifier\Query;

abstract class AbstractFilter implements FilterInterface
{
    /** @var Query */
    protected $rawQuery;

    protected $query;

    protected $relations = [];

    public function getFilteredQuery()
    {
        foreach ($this->rawQuery->getConditions() as $condition) {
            $this->setCondition($condition);
        }

        $this->makeRelations();

        return $this->query;
    }

    abstract protected function setCondition(AbstractCondition $condition): void;

    abstract protected function makeRelations(): void;
}
