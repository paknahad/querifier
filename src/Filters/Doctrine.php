<?php
namespace Paknahad\Querifier\Filters;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Paknahad\Querifier\Operators;
use Paknahad\Querifier\Parts\AbstractCondition;
use Paknahad\Querifier\Parts\Combiner;
use Paknahad\Querifier\Parts\Condition;
use Paknahad\Querifier\Query;

class Doctrine extends AbstractFilter
{
    const ROOT_ALIAS = 'r';

    protected $entityManager;
    protected $rootEntity;
    protected $fields;

    public function __construct(EntityRepository $repository, Query $filters)
    {
        $this->query = $repository->createQueryBuilder(self::ROOT_ALIAS);
        $this->entityManager = $this->query->getEntityManager();
        $this->rawQuery = $filters;

        $this->rootEntity = $this->query->getRootEntities()[0];

        $this->setAvailableFields($this->rootEntity);
    }

    /**
     * @inheritdoc
     */
    protected function setCondition(AbstractCondition $condition): void
    {
        $this->query->andWhere($this->createCondition($condition));
    }

    /**
     * @inheritdoc
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

            $operator = ($condition->getOperator() == Combiner::OR) ? 'orX' : 'andX';

            return call_user_func_array([$this->query->expr(), $operator], $conditions);
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

        if (! empty($explodedField)) {
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
            $fieldMetadata['relation_alias'] ?? self::ROOT_ALIAS,
            $fieldMetadata['fieldName']
        );
    }

    /**
     * Set value & return that parameter name
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function setValue($value, string $operator): ?string
    {
        static $iterator = 0;

        if (is_null($value) || in_array($operator, [Operators::OP_IS_NULL, Operators::OP_IS_NOT_NULL])) {
            return null;
        }

        if (in_array($operator, [Operators::OP_IN, Operators::OP_NOT_IN])) {
            $value = explode(',', $value);
        }

        $this->query->setParameter(++$iterator, $value);

        return '?' . $iterator;
    }

    /**
     * Set relation & return that alias
     *
     * @param string      $relation
     * @param null|string $alias
     *
     * @return string
     */
    protected function setRelation(string $relation, ?string $alias): string
    {
        static $iterator = 1;

        if (is_null($alias)) {
            $alias = self::ROOT_ALIAS;
        }

        if (! isset($this->relations[$alias][$relation])) {
            $newAlias = 'r__' . $iterator++;

            $this->relations[$alias][$relation] = $newAlias;
        }


        return $this->relations[$alias][$relation];
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
     * @throws EntityNotFoundException
     */
    protected function getRelationMetaData(string $entity, string $relation): array
    {
        $associations = $this->entityManager->getClassMetadata($entity)->associationMappings;

        if (! isset($associations[$relation])) {
            throw new EntityNotFoundException();
        }

        return $associations[$relation];
    }
}