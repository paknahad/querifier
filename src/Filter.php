<?php
namespace Paknahad\QueryParser;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Paknahad\QueryParser\Exception\InvalidQuery;
use Paknahad\QueryParser\Filters\Doctrine;
use Psr\Http\Message\ServerRequestInterface;

class Filter
{
    private $query;

    public function __construct(ServerRequestInterface $request)
    {
        $parser = Parser::parsFromPsrRequest($request);
        $this->query = $parser->getQuery();
    }

    public function applyFilter($query)
    {
        if ($query instanceof EntityRepository) {
            return $this->filterDoctrine($query);
        }

        throw new InvalidQuery('Unknown Query class: ' . get_class($query));
    }

    private function filterDoctrine(EntityRepository $repository): QueryBuilder
    {
        $finder = new Doctrine($repository, $this->query);

        return $finder->getFilteredQuery();
    }
}
