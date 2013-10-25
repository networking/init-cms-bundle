<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Sluggable\Util\Urlizer;

use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Entity\MenuItemRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

/**
 * @author net working AG <info@networking.ch>
 */
class UniqueURLValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Coantainer
     */
    protected $container;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(ObjectManager $em, Container $container)
    {
        $this->em = $em;
        $this->request = $container->get('request');
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {


        $pageManager = $this->container->get('networking_init_cms.page_manager');
        $url = Urlizer::urlize($value->getUrl());
        $pages = $pageManager->findBy(array('url' => $url, 'parent' => $value->getParent(), 'locale' => $value->getLocale()));

        if ($value->getParent()) {
            $url = $value->getParent()->getFullPath() . $url;
        }
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if ($page->getId() != $value->getId()) {
                    $this->context->addViolationAt('url', $constraint->message, array('{{ value }}' => $url));
                    return false;
                }
            }
        }


        return true;

    }
}
