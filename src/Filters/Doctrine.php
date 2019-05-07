<?php

namespace Paknahad\Querifier\Filters;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parts\AbstractCondition;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use Paknahad\Querifier\Query;

class Doctrine extends AbstractFilter
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    protected $rootEntity;

    /** @var string */
    protected $rootAlias;

    /** @var array */
    protected $sortingFields;

    /** @var array */
    protected $fields;

    /**
     * Doctrine constructor.
     *
     * @param QueryBuilder $query
     * @param Query        $filters
     */
    public function __construct(QueryBuilder $query, Query $filters, array $sorting)
    {
        $this->query = $query;
        $this->rootAlias = $this->query->getRootAliases()[0];
        $this->entityManager = $this->query->getEntityManager();
        $this->rawQuery = $filters;
        $this->sortingFields = $sorting;

        $this->rootEntity = $this->query->getRootEntities()[0];

        $this->setAvailableFields($this->rootEntity);
    }

    /**
     * {@inheritdoc}
     */
    protected function setCondition(AbstractCondition $condition): void
    {
        $this->query->andWhere($this->createCondition($condition));
    }

    /**
     * {@inheritdoc}
     */
    protected function makeRelations(): void
    {
        foreach ($this->relations as $sourceEntityAlias => $relations) {
            foreach ($relations as $relation => $destinationEntityAlias) {
                $this->query->leftJoin(sprintf('%s.%s', $sourceEntityAlias, $relation), $destinationEntityAlias);
            }
        }
    }

    /**
     * @param AbstractCondition $condition
     *
     * @return Comparison|Composite
     *
     * @throws EntityNotFoundException
     */
    private function createCondition(AbstractCondition $condition)
    {
        if ($condition instanceof Condition) {
            $metadata = $this->getFieldMetaData($condition->getField());

            return $this->query->expr()
                ->{Operators::getOperator($condition->getOperator(), 'doctrine')}(
                    $this->getFieldName($metadata),
                    $this->setValue($condition->getValue(), $condition->getOperator())
                );
        } elseif ($condition instanceof Combiner) {
            $conditions = [];
            foreach ($condition->getConditions() as $subCondition) {
                $conditions[] = $this->createCondition($subCondition);
            }

            $operator = (Combiner::OR == $condition->getOperator()) ? 'orX' : 'andX';

            return \call_user_func_array([$this->query->expr(), $operator], $conditions);
        }
    }

    /**
     * @param string $fieldName
     *
     * @return array
     *
     * @throws EntityNotFoundException
     */
    protected function getFieldMetaData(string $fieldName): array
    {
        $explodedField = array_reverse(explode('.', $fieldName));

        $finalField = array_shift($explodedField);
        $entity = $this->rootEntity;

        if (!empty($explodedField)) {
            $alias = null;

            foreach (array_reverse($explodedField) as $relation) {
                $relationMetaData = $this->getRelationMetaData($entity, $relation);
                $alias = $this->setRelation($relation, $alias);
                $entity = $relationMetaData['targetEntity'];
            }

            $this->setAvailableFields($entity);
        }

        if (!isset($this->fields[$entity][$finalField])) {
            throw new EntityNotFoundException();
        }

        $fieldMetaData = $this->fields[$entity][$finalField];

        if (isset($alias)) {
            $fieldMetaData['relation_alias'] = $alias;
        }

        return $fieldMetaData;
    }

    /**
     * @param array $fieldMetadata
     *
     * @return string
     */
    protected function getFieldName(array $fieldMetadata): string
    {
        return sprintf(
            '%s.%s',
            $fieldMetadata['relation_alias'] ?? $this->rootAlias,
            $fieldMetadata['fieldName']
        );
    }

    /**
     * Set value & return that parameter name.
     *
     * @param mixed  $value
     * @param string $operator
     *
     * @return string
     */
    protected function setValue($value, string $operator): ?string
    {
        static $iterator = 0;

        if (null === $value || \in_array($operator, [Operators::OP_IS_NULL, Operators::OP_IS_NOT_NULL])) {
            return null;
        }

        if (\in_array($operator, [Operators::OP_IN, Operators::OP_NOT_IN])) {
            $value = explode(',', $value);
        }

        $this->query->setParameter(++$iterator, $value);

        return '?'.$iterator;
    }

    /**
     * Set relation & return that alias.
     *
     * @param string      $relation
     * @param string|null $alias
     *
     * @return string
     */
    protected function setRelation(string $relation, ?string $alias): string
    {
        static $iterator = 1;

        if (null === $alias) {
            $alias = $this->rootAlias;
        }

        if (!isset($this->relations[$alias][$relation])) {
            $newAlias = 'r__'.$iterator++;

            $this->relations[$alias][$relation] = $newAlias;
        }

        return $this->relations[$alias][$relation];
    }

    /**
     * {@inheritdoc}
     *
     * @throws EntityNotFoundException
     */
    protected function sortQuery(): void
    {
        foreach ($this->sortingFields as $field) {
            $fieldName = $this->getFieldName($this->getFieldMetaData($field['field']));
            $this->query->addOrderBy($fieldName, $field['direction']);
        }
    }

    /**
     * @param string $entity
     */
    protected function setAvailableFields(string $entity): void
    {
        if (isset($this->fields[$entity])) {
            return;
        }

        $this->fields[$entity] = $this->entityManager->getClassMetadata($entity)->fieldMappings;
    }

    /**
     * @param string $entity
     * @param string $relation
     *
     * @return array
     *
     * @throws EntityNotFoundException
     */
    protected function getRelationMetaData(string $entity, string $relation): array
    {
        $associations = $this->entityManager->getClassMetadata($entity)->associationMappings;

        if (!isset($associations[$relation])) {
            throw new EntityNotFoundException();
        }

        return $associations[$relation];
    }
}
