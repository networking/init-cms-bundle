<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageAutocompleteType extends EntityType
{

    /**
     * @var null
     */
    protected $locale = null;

    /**
     * @var null
     */
    protected $pageId = null;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * PageAutocompleteType constructor.
     * @param ManagerRegistry $registry
     * @param RequestStack $requestStack
     */
    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        parent::__construct($registry);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        if ($locale = $this->requestStack->getCurrentRequest()->get('page_locale')) {
            $this->locale = $locale;
            return;
        }

        if (!$this->locale) {
            $this->pageId = $this->requestStack->getCurrentRequest()->get('objectId');
        }

        if ($this->pageId) {
            $queryBuilder = $this->getClosureByPageId($this->pageId);
        } else {
            $queryBuilder = $this->getClosureByLocale($this->locale);
        }

        $resolver->setDefaults(['query_builder' => $queryBuilder]);
    }

    /**
     * @param $locale
     * @return callable
     */
    public function getClosureByLocale($locale)
    {
        return $queryBuilder = function (EntityRepository $er) use ($locale) {
            $queryBuilder = $er->createQueryBuilder('p');
            $queryBuilder->andWhere('p.locale = :locale');
            $queryBuilder->setParameter('locale', $locale);

            return $queryBuilder;
        };
    }

    /**
     * @param $pageId
     * @return callable
     */
    public function getClosureByPageId($pageId)
    {
        return $queryBuilder = function (EntityRepository $er) use ($pageId) {
            $qb = $er->createQueryBuilder('p');
            $qb2 = $er->createQueryBuilder('p2');
            $qb->andWhere(
                $qb->expr()->in(
                    'p.locale',
                    $qb2->select('p2.locale')
                        ->where('p2.id = :id')->getDql()
                )
            );

            $qb->setParameter('id', $pageId);

            return $qb;
        };
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return AutocompleteType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'networking_type_page_autocomplete';
    }
}