<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Networking\InitCmsBundle\Model\HelpTextManagerInterface;

/**
 * Class HelpTextManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class HelpTextManager extends EntityRepository implements HelpTextManagerInterface
{
    public function __construct(EntityManagerInterface $om, $class)
    {
        $classMetaData = $om->getClassMetadata($class);

        parent::__construct($om, $classMetaData);
    }

    /**
     * @param $translationKey
     * @param $locale
     *
     * @return object
     */
    public function getHelpTextByKeyLocale($translationKey, $locale)
    {
        $qb = $this->createQueryBuilder('h');
        $qb->where('h.locale LIKE :locale')
            ->andWhere('h.translationKey LIKE :translationKey')
            ->orderBy('h.id', 'asc')
            ->setParameter(':locale', substr((string) $locale, 0, 2).'%')
            ->setParameter(':translationKey', $translationKey.'%');

        try {
            $helpText = $qb->getQuery()->getSingleResult();
        } catch (NoResultException) {
            $qb = $this->createQueryBuilder('h');
            $qb->where('h.locale LIKE :locale')
                ->andWhere('h.translationKey = :translationKey')
                ->orderBy('h.id', 'asc')
                ->setParameter(':locale', substr((string) $locale, 0, 2).'%')
                ->setParameter(':translationKey', 'not_found');
            $helpText = $qb->getQuery()->getSingleResult();
        }

        return $helpText;
    }

    /**
     * @param $translationKey
     * @param $locale
     *
     * @return array
     */
    public function searchHelpTextByKeyLocale($translationKey, $locale)
    {
        $qb = $this->createQueryBuilder('h');
        $qb->where('h.locale LIKE :locale')
            ->andWhere('h.translationKey LIKE :translationKey')
            ->orderBy('h.id', 'asc')
            ->setParameter(':locale', substr((string) $locale, 0, 2).'%')
            ->setParameter(':translationKey', $translationKey.'%');

        return $qb->getQuery()->getResult();
    }
}
