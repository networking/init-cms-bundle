<?php

namespace Networking\InitCmsBundle\Repository;

class TransUnitRepository extends \Lexik\Bundle\TranslationBundle\Entity\TransUnitRepository
{
    public function getAllDomainsByLocale()
    {

        return $this->createQueryBuilder('tu')
            ->select('te.locale, tu.domain')
            ->join('tu.translations', 'te')
            ->addGroupBy('te.locale')
            ->addGroupBy('tu.domain')
            ->getQuery()
            ->getArrayResult();
    }
}