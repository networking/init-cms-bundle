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
namespace Networking\InitCmsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;

/**
 * Class PageToNumberTransformer.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $pageManager;

    public function __construct(PageManagerInterface $om)
    {
        $this->pageManager = $om;
    }

    /**
     * Transforms an object (page) to a string (number).
     *
     * @param PageInterface $page
     *
     * @return mixed|string
     */
    public function transform($page)
    {
        if (null === $page) {
            return '';
        }

        return $page->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param string $id
     *
     *
     * @throws TransformationFailedException if object (issue) is not found
     */
    public function reverseTransform($id): ?\Networking\InitCmsBundle\Model\PageInterface
    {
        if (!$id) {
            return null;
        }

        $page = $this->pageManager->findById($id);

        if (null === $page) {
            throw new TransformationFailedException(sprintf(
                'An page with id "%s" does not exist!',
                $id
            ));
        }

        return $page;
    }
}
