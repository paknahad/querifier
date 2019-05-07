<?php

namespace Paknahad\Querifier;

use Doctrine\ORM\QueryBuilder;
use Paknahad\Querifier\Exception\InvalidQuery;
use Paknahad\Querifier\Filters\Doctrine;
use Psr\Http\Message\ServerRequestInterface;

class Filter
{
    private $query;

    public function __construct(ServerRequestInterface $request)
    {
        $parser = Parser::parseFromPsrRequest($request);
        $this->query = $parser->getQuery();
    }

    public function applyFilter($query)
    {
        if ($query instanceof QueryBuilder) {
            return $this->filterDoctrine($query);
        }

        throw new InvalidQuery('Unknown Query class: '.\get_class($query));
    }

    private function filterDoctrine(QueryBuilder $queryBuilder): QueryBuilder
    {
        $finder = new Doctrine($queryBuilder, $this->query);

        return $finder->getFilteredQuery();
    }
}
