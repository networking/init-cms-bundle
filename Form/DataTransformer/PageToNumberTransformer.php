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
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * Class PageToNumberTransformer
 * @package Networking\InitCmsBundle\Form\DataTransformer
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param \Networking\InitCmsBundle\Model\PageManagerInterface $om
     */
    public function __construct(\Networking\InitCmsBundle\Model\PageManagerInterface $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (page) to a string (number).
     *
     * @param PageInterface $page
     * @return mixed|string
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
     * @param  string $id
     * @return PageInterface|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $page = $this->om->findById($id);

        if (null === $page) {
            throw new TransformationFailedException(sprintf(
                'An page with id "%s" does not exist!',
                $id
            ));
        }

        return $page;
    }
}
