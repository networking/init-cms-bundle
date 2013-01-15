<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Builder;

use Sonata\DoctrineORMAdminBundle\Builder\ListBuilder as SonataDoctrineOrmListBuilder,
    Sonata\AdminBundle\Admin\FieldDescriptionInterface,
    Sonata\AdminBundle\Guesser\TypeGuesserInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

class ListBuilder extends SonataDoctrineOrmListBuilder
{

    /**
     * @param \Sonata\AdminBundle\Guesser\TypeGuesserInterface $guesser
     * @param array                                            $templates
     */
    public function __construct(TypeGuesserInterface $guesser, $templates = array())
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('NetworkingInitCmsBundle:CRUD:list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('action');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null === $fieldDescription->getOption('code')) {
            $fieldDescription->setOption('code', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('NetworkingInitCmsBundle:CRUD:list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
    }
}
