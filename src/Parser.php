<?php
namespace Paknahad\QueryParser;

use Paknahad\QueryParser\Exception\InvalidFilter;
use Paknahad\QueryParser\Parts\AbstractCondition;
use Psr\Http\Message\ServerRequestInterface;

class Parser
{
    const COMBINATION_PATTERN = '/^_cmb_(?P<operator>[a-z]{2,3})$/';

    protected $query;

    protected function __construct(array $filters, array $sort)
    {
        $this->query = new Query();

        foreach ($filters as $key => $value) {
            $this->query->addCondition($this->pars($key, $value));
        }
    }

    public static function parsFromPsrRequest(ServerRequestInterface $request): self
    {
        $params = $request->getQueryParams();

        return self::parsFromArray(
            isset($params['filter']) ? $params['filter'] : [],
            isset($params['sort']) ? $params['sort'] : []
        );
    }

    public static function parsFromArray(array $filters, array $sort): self
    {
        return new self($filters, $sort);
    }

    protected function pars($key, $value, $name = null): AbstractCondition
    {
        if (preg_match('/^_/', $key)) {
            if (preg_match(self::COMBINATION_PATTERN, $key, $matches)) {

                return Factory::makeCombiner($matches['operator'], $value, $name);
            } elseif (is_null($name) && is_array($value)) {
                reset($value);
                $newKey = key($value);

                return $this->pars($newKey, $value[$newKey], $key);
            }

            throw new InvalidFilter();
        }

        return Factory::makeCondition($key, $value, $name);
    }

    public function getQuery(): Query
    {
        return $this->query->rearrange();
    }
}
