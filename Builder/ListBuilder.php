<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Builder;

use Sonata\DoctrineORMAdminBundle\Builder\ListBuilder as SonataDoctrineOrmListBuilder;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

/**
 * Class ListBuilder
 * @package Networking\InitCmsBundle\Builder
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ListBuilder extends SonataDoctrineOrmListBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(TypeGuesserInterface $guesser, $templates = array())
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
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
