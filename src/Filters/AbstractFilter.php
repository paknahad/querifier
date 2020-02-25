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

    /**
     * @return mixed
     */
    public function getFilteredQuery()
    {
        foreach ($this->rawQuery->getConditions() as $condition) {
            $this->setCondition($condition);
        }

        $this->sortQuery();

        $this->makeRelations();

        return $this->query;
    }

    /**
     * Add condition to query.
     *
     * @param AbstractCondition $condition
     */
    abstract protected function setCondition(AbstractCondition $condition): void;

    /**
     * Join the relations to query.
     */
    abstract protected function makeRelations(): void;

    /**
     * Apply sorting.
     */
    abstract protected function sortQuery(): void;
}
