<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Admin;

use Sonata\UserBundle\Admin\Entity\GroupAdmin as SonataGroupAdmin,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Datagrid\ListMapper,
    Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GroupAdmin extends SonataGroupAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/user/groups';

    /**
     * @var array
     */
    protected $trackedActions = array('list');


    /**
     * @param $trackedActions
     * @return BaseAdmin
     */
    public function setTrackedActions($trackedActions)
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return Array
     */
    public function getTrackedActions()
    {
        return $this->trackedActions;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'icon-group';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add(
            'roles',
            'sonata_security_roles',
            array(
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label_render' => false
            )
        );
    }
}
