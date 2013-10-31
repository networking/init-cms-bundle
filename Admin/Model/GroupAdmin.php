<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Admin\Model;

use Sonata\UserBundle\Admin\Entity\GroupAdmin as SonataGroupAdmin;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class GroupAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class GroupAdmin extends SonataGroupAdmin
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
     * @return $this
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
                'required' => false
            )
        );
    }
}
