<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This file is part of the schuler-shop  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ORMQueryBuilderLoader implements EntityLoaderInterface
{


    public function __construct(
        /**
         * Contains the query builder that builds the query for fetching the
         * entities.
         *
         * This property should only be accessed through queryBuilder.
         */
        private readonly QueryBuilder $queryBuilder
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities(): array
    {
        return $this->queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitiesByIds($identifier, array $values): array
    {
        $qb = clone $this->queryBuilder;
        $alias = current($qb->getRootAliases());
        $parameter = 'ORMQueryBuilderLoader_getEntitiesByIds_'.$identifier;
        $parameter = str_replace('.', '_', $parameter);
        $where = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        // Guess type
        $entity = current($qb->getRootEntities());
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        if (in_array($metadata->getTypeOfField($identifier), ['integer', 'bigint', 'smallint'])) {
            $parameterType = Connection::PARAM_INT_ARRAY;

            // Filter out non-integer values (e.g. ""). If we don't, some
            // databases such as PostgreSQL fail.
            $values = array_values(array_filter($values, fn($v) => (string) $v === (string) (int) $v));
        } elseif ('guid' === $metadata->getTypeOfField($identifier)) {
            $parameterType = Connection::PARAM_STR_ARRAY;

            // Like above, but we just filter out empty strings.
            $values = array_values(array_filter($values, fn($v) => (string) $v !== ''));
        } else {
            $parameterType = Connection::PARAM_STR_ARRAY;
        }
        if (!$values) {
            return [];
        }

        return $qb->andWhere($where)
            ->getQuery()
            ->setParameter($parameter, $values, $parameterType)
            ->getResult();
    }

}
