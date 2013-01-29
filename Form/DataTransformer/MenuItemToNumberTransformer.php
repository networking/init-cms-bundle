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
use Networking\InitCmsBundle\Entity\MenuItem;

/**
 * @author net working AG <info@networking.ch>
 */
class MenuItemToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (menu item) to a string (number).
     *
     * @param  MenuItem|null $id
     * @return string
     */
    public function transform($menuItem)
    {

        if (null === $menuItem || false === $menuItem) {
            return "";
        }


        return $menuItem->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string                        $id
     * @return MenuItem|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $menuItem = $this->om
            ->getRepository('NetworkingInitCmsBundle:MenuItem')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $menuItem) {
            throw new TransformationFailedException(sprintf(
                'An page with id "%s" does not exist!',
                $id
            ));
        }

        return $menuItem;
    }
}
