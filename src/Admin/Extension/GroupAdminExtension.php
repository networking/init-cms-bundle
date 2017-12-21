<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 20.12.17
 * Time: 16:38
 */

namespace Networking\InitCmsBundle\Admin\Extension;


use Sonata\AdminBundle\Admin\AbstractAdminExtension;

class GroupAdminExtension extends AbstractAdminExtension
{


    /**
     * @var array
     */
    protected $trackedActions = ['list'];


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
     * @return array
     */
    public function getTrackedActions()
    {
        return $this->trackedActions;
    }
}