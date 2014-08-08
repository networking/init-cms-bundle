<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Form\DataTransformer;


use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ModelsToStringListTransformer  implements DataTransformerInterface
{
    protected $choiceList;

    /**
     * @param \Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList $choiceList
     */
    public function __construct(ModelChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }

        $array = array();

        if (count($this->choiceList->getIdentifier()) > 1) {
            // load all choices
            $availableEntities = $this->choiceList->getEntities();

            foreach ($collection as $entity) {
                // identify choices by their collection key
                $key = array_search($entity, $availableEntities);
                $array[] = $key;
            }
        } else {
            foreach ($collection as $entity) {
                $array[] = current($this->choiceList->getIdentifierValues($entity));
            }
        }

        return implode(', ', $array);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($string)
    {
        $collection = $this->choiceList->getModelManager()->getModelCollectionInstance(
            $this->choiceList->getClass()
        );

        if (!$collection instanceof \ArrayAccess) {
            throw new UnexpectedTypeException($collection, '\ArrayAccess');
        }

        if ('' === $string || null === $string) {
            return $collection;
        }

        if (is_array($string)) {
            throw new UnexpectedTypeException($string, 'string');
        }

        $notFound = array();

        $keys = explode(',', $string);
        // optimize this into a SELECT WHERE IN query
        foreach ($keys as $key => $value) {
            if ($entity = $this->choiceList->getEntity($key)) {
                $collection[] = $entity;
            } else {
                $notFound[] = $key;
            }
        }

        if (count($notFound) > 0) {
            throw new TransformationFailedException(sprintf('The entities with keys "%s" could not be found', implode('", "', $notFound)));
        }

        return $collection;
    }


}