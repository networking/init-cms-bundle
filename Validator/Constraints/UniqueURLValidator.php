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

use Gedmo\Sluggable\Util\Urlizer;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

/**
 * Class UniqueURLValidator
 * @package Networking\InitCmsBundle\Validator\Constraints
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UniqueURLValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $om;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     * @param PageManagerInterface $pageManager
     */
    public function __construct(Request $request, PageManagerInterface $pageManager)
    {
        $this->request = $request;
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {

        $url = Urlizer::urlize($value->getUrl());
        $pages = $this->pageManager->findBy(array('url' => $url, 'parent' => $value->getParent(), 'locale' => $value->getLocale()));

        if ($value->getParent()) {
            $url = $value->getParent()->getFullPath() . $url;
        }
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                /** @var \Networking\InitCmsBundle\Model\PageInterface $page */
                if ($page->getId() != $value->getId()) {
                    $this->context->addViolationAt('url', $constraint->message, array('{{ value }}' => $url));
                    return false;
                }
            }
        }


        return true;

    }
}
