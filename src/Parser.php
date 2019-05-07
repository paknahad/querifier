<?php

namespace Paknahad\Querifier;

use Paknahad\Querifier\Exception\InvalidFilter;
use Paknahad\Querifier\Parts\AbstractCondition;
use Psr\Http\Message\ServerRequestInterface;

class Parser
{
    const COMBINATION_PATTERN = '/^_cmb_(?P<operator>[a-z]{2,3})$/';

    /** @var Query */
    protected $query;

    /** @var array */
    protected $sortingFields = [];

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

    public static function parseFromPsrRequest(ServerRequestInterface $request): self
    {
        $params = $request->getQueryParams();

        return self::parseFromArray(
            isset($params['filter']) ? $params['filter'] : [],
            isset($params['sort']) ? explode(',', $params['sort']) : []
        );
    }

    public static function parseFromArray(array $filters, array $sort): self
    {
        return new self($filters, $sort);
    }

    /**
     * Process an individual field.
     *
     * @param string $field
     *
     * @return array
     */
    protected function parseSortingFields(string $field): array
    {
        $direction = 'ASC';
        if ('-' !== $field[0]) {
            $field = ltrim($field, '-');
            $direction = 'DESC';
        }

        return [
            'field' => $field,
            'direction' => $direction,
        ];
    }

    protected function parseConditions($key, $value, $name = null): AbstractCondition
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

    public function getQuery(): Query
    {
        return $this->query->rearrange();
    }

    /**
     * Get array of fields for sorting.
     *
     * @return array
     */
    public function getSorting(): array
    {
        return $this->sortingFields;
    }
}
