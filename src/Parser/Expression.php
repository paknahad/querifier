<?php

namespace Paknahad\Querifier\Parser;

use Paknahad\Querifier\Exception\InvalidQueryString;
use Paknahad\Querifier\Factory;
use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use Paknahad\Querifier\Parts\ConditionInterface;
use Paknahad\Querifier\Query;
use Psr\Http\Message\ServerRequestInterface;

class Expression extends AbstractParser
{
    const LOGICAL_OPERATOR_OR = '|';
    const LOGICAL_OPERATOR_AND = '^';

    const CONDITION_NAME_PREFIX = '___C___';

    const LOGICAL_OPERATORS = [
        Combiner::AND => self::LOGICAL_OPERATOR_AND,
        Combiner::OR => self::LOGICAL_OPERATOR_OR,
    ];

    const COMPARISON_OPERATORS = [
        '<>' => Operators::OP_NOT_EQUAL,
        ':' => Operators::OP_EQUAL,
        '>' => Operators::OP_GRATER_THAN,
        '<' => Operators::OP_LOWER_THAN,
    ];

    /** @var Query */
    protected $query;

    private $parts = [];

    protected function __construct(string $queryString, array $sort)
    {
        $this->query = new Query();

        $this->generateQuery($queryString);
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

        return self::parseFromString(
            isset($params['q']) ? $params['q'] : '',
            isset($params['sort']) ? $params['sort'] : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function parseFromString(string $queryString, string $sort = null): AbstractParser
    {
        return new self(
            $queryString,
            !is_null($sort) ? explode(',', $sort) : []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): Query
    {
        return $this->query->rearrange();
    }

    /**
     * @param string $queryString
     *
     * @throws InvalidQueryString
     * @throws \Paknahad\Querifier\Exception\InvalidFilter
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     */
    private function generateQuery(string $queryString): void
    {
        static $i;

        $start = strrpos($queryString, '(');

        $end = strpos($queryString, ')', $start);

        if (false === $start && false !== $end) {
            throw new InvalidQueryString(sprintf('Invalid parenthesis at col %d.', $end + 1));
        } elseif (false !== $start && false === $end) {
            throw new InvalidQueryString(sprintf('Invalid parenthesis at col %d.', $start + 1));
        } elseif (false === $start) {
            $result = $queryString;
        } else {
            $result = substr($queryString, $start + 1, $end - $start - 1);
        }

        $conditionName = self::CONDITION_NAME_PREFIX.++$i;

        $this->parts[$conditionName] = $this->generateCombination($result);

        if (false !== $start) {
            $queryString = substr_replace($queryString, $conditionName, $start, $end - $start + 1);
            $this->generateQuery($queryString);
        }
    }

    /**
     * @param string $conditions
     * @param string $operator
     *
     * @return Combiner
     *
     * @throws \Paknahad\Querifier\Exception\InvalidFilter
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     * @throws InvalidQueryString
     */
    private function generateCombination(string $conditions, string $operator = Combiner::OR): ConditionInterface
    {
        $parts = explode(self::LOGICAL_OPERATORS[$operator], $conditions);

        $conditionsName = [];
        foreach ($parts as &$part) {
            $part = (Combiner::AND == $operator) ?
                $this->generateCondition($part) : $this->generateCombination($part, Combiner::AND);

            $conditionsName[] = $part->getName();
        }

        if (1 == count($parts)) {
            return $parts[0];
        }

        $combiner = Factory::makeCombiner($operator, implode(',', $conditionsName));
        $this->query->addCondition($combiner);

        return $combiner;
    }

    /**
     * @param string $query
     *
     * @return ConditionInterface
     *
     * @throws \Paknahad\Querifier\Exception\InvalidOperator
     * @throws InvalidQueryString
     */
    private function generateCondition(string $query): ConditionInterface
    {
        if (self::CONDITION_NAME_PREFIX == substr($query, 0, strlen(self::CONDITION_NAME_PREFIX))) {
            return $this->parts[$query];
        }

        $parts = $this->explodeCondition($query);

        $condition = new Condition($parts['field'], $parts['operator'], $parts['value']);
        $this->query->addCondition($condition);

        return $condition;
    }

    /**
     * @param string $query
     *
     * @return array
     *
     * @throws InvalidQueryString
     */
    private function explodeCondition(string $query): array
    {
        foreach (self::COMPARISON_OPERATORS as $operator => $name) {
            if (strpos($query, $operator)) {
                $parts = explode($operator, $query);

                $operator = $this->findOperator($name, $parts[1]);

                return [
                    'field' => $parts[0],
                    'value' => $parts[1],
                    'operator' => $operator,
                ];
            }
        }

        throw new InvalidQueryString('Invalid condition: '.$query);
    }

    /**
     * @param string $operator
     * @param string $value
     *
     * @return string
     */
    private function findOperator(string $operator, string &$value): string
    {
        if (in_array($operator, [Operators::OP_NOT_EQUAL, Operators::OP_EQUAL])) {
            if ('null' === strtolower($value)) {
                return (Operators::OP_EQUAL == $operator) ? Operators::OP_IS_NULL : Operators::OP_IS_NOT_NULL;
            } elseif (false !== strpos($value, '%')) {
                return (Operators::OP_EQUAL == $operator) ? Operators::OP_LIKE : Operators::OP_NOT_LIKE;
            } elseif ('[' == substr($value, 0, 1) && ']' == substr($value, -1, 1)) {
                $value = rtrim(ltrim($value, '['), ']');
                return (Operators::OP_EQUAL == $operator) ? Operators::OP_IN : Operators::OP_NOT_IN;
            }
        }

        return $operator;
    }
}
