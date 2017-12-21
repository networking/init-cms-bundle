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

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Networking\InitCmsBundle\Entity\MenuItem;

/**
 * Class MenuItemToNumberTransformer
 * @package Networking\InitCmsBundle\Form\DataTransformer
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItemToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var string
     */
    private $class;

    /**
     * MenuItemToNumberTransformer constructor.
     * @param ModelManagerInterface $modelManager
     * @param $class
     */
    public function __construct(ModelManagerInterface $modelManager, $class)
    {
        $this->modelManager = $modelManager;
        $this->class = $class;
    }

    /**
     * Transforms an object (menu item) to a string (number).
     *
     * @param mixed $menuItem
     * @return mixed|string
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
     * @param  string $id
     * @return MenuItem|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $menuItem = $this->modelManager->find($this->class, $id);

        if (null === $menuItem) {
            throw new TransformationFailedException(sprintf(
                'An page with id "%s" does not exist!',
                $id
            ));
        }

        return $menuItem;
    }
}
