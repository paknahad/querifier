<?php

namespace Paknahad\Querifier;

use Paknahad\Querifier\Exception\InvalidFilter;
use Paknahad\Querifier\Parts\AbstractCondition;
use Psr\Http\Message\ServerRequestInterface;

class Parser
{
    const COMBINATION_PATTERN = '/^_cmb_(?P<operator>[a-z]{2,3})$/';

    protected $query;

    protected function __construct(array $filters, array $sort)
    {
        $this->query = new Query();

        foreach ($filters as $key => $value) {
            $this->query->addCondition($this->parse($key, $value));
        }
    }

    public static function parseFromPsrRequest(ServerRequestInterface $request): self
    {
        $params = $request->getQueryParams();

        return self::parseFromArray(
            isset($params['filter']) ? $params['filter'] : [],
            isset($params['sort']) ? $params['sort'] : []
        );
    }

    public static function parseFromArray(array $filters, array $sort): self
    {
        return new self($filters, $sort);
    }

    protected function parse($key, $value, $name = null): AbstractCondition
    {
        if (preg_match('/^_/', $key)) {
            if (preg_match(self::COMBINATION_PATTERN, $key, $matches)) {
                return Factory::makeCombiner($matches['operator'], $value, $name);
            } elseif (null === $name && \is_array($value)) {
                reset($value);
                $newKey = key($value);

                return $this->parse($newKey, $value[$newKey], $key);
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
