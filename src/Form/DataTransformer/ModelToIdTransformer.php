<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Networking\InitCmsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelToIdTransformer implements DataTransformerInterface
{
    protected $modelManager;

    /**
     * @param string                $className
     */
    public function __construct(ModelManagerInterface $modelManager, protected $className)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($newId)
    {
        if (empty($newId) && !in_array($newId, ['0', 0], true)) {
            return null;
        }

        return $this->modelManager->find($this->className, $newId);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($entity)
    {
        if (empty($entity)) {
            return null;
        }

        return $entity->getId();
    }
}
