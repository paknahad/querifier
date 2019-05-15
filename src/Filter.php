<?php

namespace Paknahad\Querifier;

use Doctrine\ORM\QueryBuilder;
use Paknahad\Querifier\Exception\InvalidQuery;
use Paknahad\Querifier\Filters\Doctrine;
use Paknahad\Querifier\Parser\AbstractParser;
use Paknahad\Querifier\Parser\Criteria;
use Paknahad\Querifier\Parser\Expression;
use Psr\Http\Message\ServerRequestInterface;

class Filter
{
    private $query;
    private $sortingFields;

    public function __construct(ServerRequestInterface $request)
    {
        $parser = $this->getParser($request);

        $this->query = $parser->getQuery();
        $this->sortingFields = $parser->getSorting();
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
        $finder = new Doctrine($queryBuilder, $this->query, $this->sortingFields);

        return $finder->getFilteredQuery();
    }

    private function getParser(ServerRequestInterface $request): AbstractParser
    {
        $params = $request->getQueryParams();

        if (isset($params['q'])) {
            return Expression::parseFromPsrRequest($request);
        }

        return Criteria::parseFromPsrRequest($request);
    }
}
