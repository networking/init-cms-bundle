<?php
namespace Networking\InitCmsBundle\Form;

use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader as BaseQueryBuilderLoader;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * This file is part of the schuler-shop  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ORMQueryBuilderLoader extends BaseQueryBuilderLoader
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
    protected $hints = array();

    /**
     * Construct an ORM Query Builder Loader.
     *
     * @param QueryBuilder|\Closure $queryBuilder The query builder or a closure
     *                                            for creating the query builder.
     *                                            Passing a closure is
     *                                            deprecated and will not be
     *                                            supported anymore as of
     *                                            Symfony 3.0.
     * @param ObjectManager         $manager      Deprecated.
     * @param string                $class        Deprecated.
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
                throw new UnexpectedTypeException($manager, 'Doctrine\Common\Persistence\ObjectManager');
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

        foreach ($this->hints as $name => $value)
        {
            $query->setHint($name, $value);
        }

        return $query->execute();
    }

    public function setHint($name, $value)
    {
        $this->hints[$name] = $value;
    }
}