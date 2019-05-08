<?php

namespace Paknahad\Querifier\Parser;

use Paknahad\Querifier\Exception\InvalidFilter;
use Paknahad\Querifier\Factory;
use Paknahad\Querifier\Parts\AbstractCondition;
use Paknahad\Querifier\Query;
use Psr\Http\Message\ServerRequestInterface;

class Criteria extends AbstractParser
{
    const COMBINATION_PATTERN = '/^_cmb_(?P<operator>[a-z]{2,3})$/';

    /** @var Query */
    protected $query;

    /**
     * Separated constructor.
     *
     * @param array $filters
     * @param array $sort
     *
     * @throws InvalidFilter
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     */
    protected function __construct(array $filters, array $sort)
    {
        $this->query = new Query();

        foreach ($filters as $key => $value) {
            $this->query->addCondition($this->parseConditions($key, $value));
        }

        foreach ($sort as $value) {
            $this->sortingFields[] = $this->parseSortingFields($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function parseFromPsrRequest(ServerRequestInterface $request): AbstractParser
    {
        $params = $request->getQueryParams();

        return self::parseFromArray(
            isset($params['filter']) ? $params['filter'] : [],
            isset($params['sort']) ? explode(',', $params['sort']) : []
        );
    }

    /**
     * @param array $filters
     * @param array $sort
     *
     * @return Criteria
     *
     * @throws InvalidFilter
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     */
    public static function parseFromArray(array $filters, array $sort): self
    {
        return new self($filters, $sort);
    }

    /**
     * @param string      $key
     * @param mixed       $value
     * @param string|null $name
     *
     * @return AbstractCondition
     *
     * @throws InvalidFilter
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     */
    protected function parseConditions(string $key, $value, $name = null): AbstractCondition
    {
        if (preg_match('/^_/', $key)) {
            if (preg_match(self::COMBINATION_PATTERN, $key, $matches)) {
                return Factory::makeCombiner($matches['operator'], $value, $name);
            } elseif (null === $name && \is_array($value)) {
                reset($value);
                $newKey = key($value);

                return $this->parseConditions($newKey, $value[$newKey], $key);
            }

            throw new InvalidFilter();
        }

        return Factory::makeCondition($key, $value, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): Query
    {
        return $this->query->rearrange();
    }
}
