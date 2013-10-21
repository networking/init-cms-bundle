<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class PageToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(\Networking\InitCmsBundle\Model\PageManagerInterface $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (page) to a string (number).
     *
     * @param  Page|null $id
     * @return string
     */
    public function transform($page)
    {
        if (null === $page) {
            return "";
        }

        return $page->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string                        $id
     * @return Page|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $page = $this->om->findOneBy(array('id' => $id))
        ;

        if (null === $page) {
            throw new TransformationFailedException(sprintf(
                'An page with id "%s" does not exist!',
                $id
            ));
        }

        return $page;
    }
}
