<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Model\HelpTextManagerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class HelpTextManager
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class HelpTextManager extends EntityRepository implements HelpTextManagerInterface
{

    public function __construct(EntityManager $em, $class)
    {
        $classMetaData = $em->getClassMetadata($class);

        parent::__construct($em, $classMetaData);
    }


    /**
     * @param $translationKey
     * @param $locale
     * @return object
     */
    public function getHelpTextByKeyLocale($translationKey, $locale)
    {
        $helpText = $this->findOneBy(array('translationKey' => $translationKey, 'locale' => $locale));
        if ($helpText === null) {
            $helpText = $this->findOneBy(array('translationKey' => 'not_found', 'locale' => $locale));
        }

        return $helpText;
    }

    /**
     * @param $translationKey
     * @param $locale
     * @return array
     */
    public function searchHelpTextByKeyLocale($translationKey, $locale)
    {
        $qb = $this->createQueryBuilder('h');
        $qb->where('h.locale = :locale')
            ->andWhere('h.translationKey LIKE :translationKey')
            ->orderBy('h.id', 'asc')
            ->setParameter(':locale', $locale)
            ->setParameter(':translationKey', $translationKey . "%");

        return $qb->getQuery()->getResult();
    }
}
