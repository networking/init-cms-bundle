<?php

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
    /**
     * Contains the query builder that builds the query for fetching the
     * entities.
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var array
     */
    protected $hints = [];

    /**
     * Construct an ORM Query Builder Loader.
     *
     * @param QueryBuilder|\Closure $queryBuilder The query builder or a closure
     *                                            for creating the query builder.
     *                                            Passing a closure is
     *                                            deprecated and will not be
     *                                            supported anymore as of
     *                                            Symfony 3.0
     * @param ObjectManager         $manager      Deprecated
     * @param string                $class        Deprecated
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($queryBuilder, $manager = null, $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            @trigger_error('Passing a QueryBuilder closure to '.__CLASS__.'::__construct() is deprecated since version 2.7 and will be removed in 3.0.', E_USER_DEPRECATED);

            if (!$manager instanceof ObjectManager) {
                throw new UnexpectedTypeException($manager, 'Doctrine\Persistence\ObjectManager');
            }

            @trigger_error('Passing an EntityManager to '.__CLASS__.'::__construct() is deprecated since version 2.7 and will be removed in 3.0.', E_USER_DEPRECATED);
            @trigger_error('Passing a class to '.__CLASS__.'::__construct() is deprecated since version 2.7 and will be removed in 3.0.', E_USER_DEPRECATED);

            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        $query = $this->queryBuilder->getQuery();

        foreach ($this->hints as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitiesByIds($identifier, array $values)
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
            $values = array_values(array_filter($values, function ($v) {
                return (string) $v === (string) (int) $v;
            }));
        } elseif ('guid' === $metadata->getTypeOfField($identifier)) {
            $parameterType = Connection::PARAM_STR_ARRAY;

            // Like above, but we just filter out empty strings.
            $values = array_values(array_filter($values, function ($v) {
                return (string) $v !== '';
            }));
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

    public function setHint($name, $value)
    {
        $this->hints[$name] = $value;
    }
}
