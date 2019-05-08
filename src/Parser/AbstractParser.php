<?php

namespace Paknahad\Querifier\Parser;

use Paknahad\Querifier\Query;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractParser
{
    /** @var Query */
    protected $query;

    /** @var array */
    protected $sortingFields = [];

    /**
     * Get query from request and parse that
     *
     * @param ServerRequestInterface $request
     *
     * @return AbstractParser
     */
    abstract public static function parseFromPsrRequest(ServerRequestInterface $request): self;

    /**
     * Return parsed query
     *
     * @return Query
     */
    abstract public function getQuery(): Query;

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
